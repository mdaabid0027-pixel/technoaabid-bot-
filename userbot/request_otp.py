#!/usr/bin/env python3
import os
import json
import asyncio
from telethon import TelegramClient

API_ID = 0  # Replace with your Telegram API ID
API_HASH = "YOUR_API_HASH"  # Replace with your Telegram API HASH
PHONE_NUMBER = "+91XXXXXXXXXX"  # Replace with your phone number
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SESSION_PATH = os.path.join(BASE_DIR, "userbot_session")
HASH_FILE = os.path.join(BASE_DIR, "login_hash.json")

async def request_code():
    client = TelegramClient(SESSION_PATH, API_ID, API_HASH)
    await client.connect()
    
    if await client.is_user_authorized():
        me = await client.get_me()
        print(f"ALREADY_AUTHORIZED: {me.first_name}")
        await client.disconnect()
        return

    try:
        sent = await client.send_code_request(PHONE_NUMBER)
        with open(HASH_FILE, "w") as f:
            json.dump({"phone_code_hash": sent.phone_code_hash}, f)
        print(f"OTP_SENT_SUCCESSFULLY")
    except Exception as e:
        print(f"ERROR: {e}")
    finally:
        await client.disconnect()

if __name__ == "__main__":
    asyncio.run(request_code())
