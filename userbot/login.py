#!/usr/bin/env python3
import sys
import asyncio
from telethon import TelegramClient

API_ID = 0  # Replace with your Telegram API ID
API_HASH = "YOUR_API_HASH"  # Replace with your Telegram API HASH
PHONE_NUMBER = "+91XXXXXXXXXX"  # Replace with your phone number
SESSION_PATH = "userbot/userbot_session"

async def login_with_code(code, pwd=None):
    client = TelegramClient(SESSION_PATH, API_ID, API_HASH)
    await client.connect()
    
    if await client.is_user_authorized():
        me = await client.get_me()
        print(f"ALREADY_LOGGED_IN: {me.first_name} (@{me.username})")
        await client.disconnect()
        return

    try:
        sent = await client.send_code_request(PHONE_NUMBER)
        phone_code_hash = sent.phone_code_hash
    except Exception as e:
        print(f"REQUEST_CODE_ERR: {e}")
        phone_code_hash = None

    try:
        await client.sign_in(PHONE_NUMBER, code=code, phone_code_hash=phone_code_hash)
        me = await client.get_me()
        print(f"LOGIN_SUCCESS: {me.first_name} (@{me.username})")
    except Exception as e:
        if "password" in str(e).lower() or "two-step" in str(e).lower() or "SessionPasswordNeededError" in type(e).__name__:
            if pwd:
                await client.sign_in(password=pwd)
                me = await client.get_me()
                print(f"LOGIN_SUCCESS: {me.first_name} (@{me.username})")
            else:
                print("PASSWORD_REQUIRED")
        else:
            print(f"LOGIN_ERROR: {e}")
    finally:
        await client.disconnect()

if __name__ == "__main__":
    if len(sys.argv) > 1:
        code_input = sys.argv[1]
        pwd_input = sys.argv[2] if len(sys.argv) > 2 else None
        asyncio.run(login_with_code(code_input, pwd_input))
    else:
        print("Usage: python3 login.py <CODE> [2FA_PASSWORD]")
