#!/usr/bin/env python3
import os
import sys
import json
import asyncio
from telethon import TelegramClient

API_ID = 0  # Replace with your Telegram API ID
API_HASH = "YOUR_API_HASH"  # Replace with your Telegram API HASH
PHONE_NUMBER = "+91XXXXXXXXXX"  # Replace with your phone number
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SESSION_PATH = os.path.join(BASE_DIR, "userbot_session")
HASH_FILE = os.path.join(BASE_DIR, "login_hash.json")

async def verify_code(code, pwd=None):
    if not os.path.exists(HASH_FILE):
        print("NO_HASH_FOUND: Please run request_otp.py first.")
        return

    with open(HASH_FILE, "r") as f:
        data = json.load(f)
    phone_code_hash = data.get("phone_code_hash")

    client = TelegramClient(SESSION_PATH, API_ID, API_HASH)
    await client.connect()

    try:
        await client.sign_in(PHONE_NUMBER, code=code.strip(), phone_code_hash=phone_code_hash)
        me = await client.get_me()
        print(f"VERIFY_SUCCESS: {me.first_name} (@{me.username})")
        if os.path.exists(HASH_FILE):
            os.remove(HASH_FILE)
    except Exception as e:
        if "password" in str(e).lower() or "two-step" in str(e).lower() or "SessionPasswordNeededError" in type(e).__name__:
            if pwd:
                await client.sign_in(password=pwd.strip())
                me = await client.get_me()
                print(f"VERIFY_SUCCESS: {me.first_name} (@{me.username})")
                if os.path.exists(HASH_FILE):
                    os.remove(HASH_FILE)
            else:
                print("PASSWORD_REQUIRED")
        else:
            print(f"VERIFY_ERROR: {e}")
    finally:
        await client.disconnect()

if __name__ == "__main__":
    if len(sys.argv) > 1:
        code_in = sys.argv[1]
        pwd_in = sys.argv[2] if len(sys.argv) > 2 else None
        asyncio.run(verify_code(code_in, pwd_in))
    else:
        print("Usage: python3 verify_otp.py <CODE> [PASSWORD]")
