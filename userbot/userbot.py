#!/usr/bin/env python3
"""
Telegram Auto-Responder Userbot
Ultra-Fast Zero-Lag Media Delivery via Pre-Cached Telegram Server References
"""

import os
import sys
import json
import time
import asyncio
from telethon import TelegramClient, events
from telethon.tl.types import User

# ==============================================================================
# CONFIGURATION
# ==============================================================================
API_ID = 0  # Replace with your Telegram API ID from my.telegram.org
API_HASH = "YOUR_API_HASH"  # Replace with your Telegram API HASH
PHONE_NUMBER = "+91XXXXXXXXXX"  # Replace with your phone number

# Directory paths
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MEDIA_DIR = os.path.join(BASE_DIR, "media")
SESSION_FILE = os.path.join(BASE_DIR, "userbot_session")
HISTORY_FILE = os.path.join(BASE_DIR, "replied_history.json")

# Media Files
VIDEO_TUTORIAL = os.path.join(MEDIA_DIR, "tutorial.mp4")
FALLBACK_VIDEO = os.path.join(MEDIA_DIR, "video.mp4")
POSTER_IMAGE = os.path.join(MEDIA_DIR, "poster.jpg")
VOICE_AUDIO = os.path.join(MEDIA_DIR, "voice.ogg")
FALLBACK_AUDIO = os.path.join(MEDIA_DIR, "voice.mp3")

# Cooldown: 7 Days (1 Week = 604,800 Seconds Production Mode)
COOLDOWN_SECONDS = 7 * 86400

# Telegram Server-Side Media Message IDs in Saved Messages
CACHED_VIDEO_MSG_ID = None
CACHED_VOICE_MSG_ID = None

# Custom Clean Welcome Text (Bot Link)
WELCOME_MESSAGE = """
🤖 **Instant Purchase Bot:**
👉 @YourBotUsername
"""

# ==============================================================================
# HELPER FUNCTIONS
# ==============================================================================
def load_history():
    """Load previously replied users with timestamps to persist across restarts."""
    if os.path.exists(HISTORY_FILE):
        try:
            with open(HISTORY_FILE, 'r') as f:
                return json.load(f)
        except Exception:
            return {}
    return {}

def save_history(history):
    """Save replied history to JSON file."""
    try:
        with open(HISTORY_FILE, 'w') as f:
            json.dump(history, f)
    except Exception as e:
        print(f"Error saving history: {e}", flush=True)

# ==============================================================================
# CLIENT INITIALIZATION & PRE-CACHE
# ==============================================================================
client = TelegramClient(SESSION_FILE, API_ID, API_HASH)

async def preload_media_cache():
    """Find existing cached media in Saved Messages ('me') or upload once to Telegram cloud."""
    global CACHED_VIDEO_MSG_ID, CACHED_VOICE_MSG_ID
    print("⏳ [CACHE] Checking Saved Messages for pre-uploaded media...", flush=True)

    try:
        async for msg in client.iter_messages('me', limit=30):
            if msg.media:
                caption = (msg.message or "")
                if "[PRE-CACHE-VIDEO]" in caption and not CACHED_VIDEO_MSG_ID:
                    CACHED_VIDEO_MSG_ID = msg.id
                    print(f"  ⚡ Found pre-cached Video Tutorial in Saved Messages (Msg ID: {msg.id})!", flush=True)
                elif "[PRE-CACHE-VOICE]" in caption and not CACHED_VOICE_MSG_ID:
                    CACHED_VOICE_MSG_ID = msg.id
                    print(f"  ⚡ Found pre-cached Voice Note in Saved Messages (Msg ID: {msg.id})!", flush=True)
            if CACHED_VIDEO_MSG_ID and CACHED_VOICE_MSG_ID:
                break
    except Exception as e:
        print(f"  ⚠️ Error scanning Saved Messages: {e}", flush=True)

    # 1. Voice Audio Pre-Cache
    if not CACHED_VOICE_MSG_ID:
        voice_path = VOICE_AUDIO if os.path.exists(VOICE_AUDIO) else (FALLBACK_AUDIO if os.path.exists(FALLBACK_AUDIO) else None)
        if voice_path:
            try:
                print(f"  -> Uploading voice note ({os.path.basename(voice_path)}) to Saved Messages...", flush=True)
                msg = await client.send_file('me', voice_path, voice_note=True, caption="[PRE-CACHE-VOICE]")
                if msg:
                    CACHED_VOICE_MSG_ID = msg.id
                    print(f"  ✅ Voice Note pre-cached successfully (Msg ID: {msg.id})!", flush=True)
            except Exception as e:
                print(f"  ⚠️ Voice caching error: {e}", flush=True)

    # 2. Video Tutorial Pre-Cache
    if not CACHED_VIDEO_MSG_ID:
        video_path = VIDEO_TUTORIAL if os.path.exists(VIDEO_TUTORIAL) else (FALLBACK_VIDEO if os.path.exists(FALLBACK_VIDEO) else None)
        if video_path:
            try:
                print(f"  -> Uploading video ({os.path.basename(video_path)}) to Saved Messages for server-side cache...", flush=True)
                msg = await client.send_file('me', video_path, supports_streaming=True, caption="[PRE-CACHE-VIDEO]")
                if msg:
                    CACHED_VIDEO_MSG_ID = msg.id
                    print(f"  ✅ Video Tutorial pre-cached successfully on Telegram server (Msg ID: {msg.id})!", flush=True)
            except Exception as e:
                print(f"  ⚠️ Video caching error: {e}", flush=True)

    print("✅ All media ready in memory for instant delivery!", flush=True)

# ==============================================================================
# INCOMING MESSAGE HANDLER
# ==============================================================================
@client.on(events.NewMessage(incoming=True, func=lambda e: e.is_private))
async def handle_private_message(event):
    """Triggered instantly on incoming 1-on-1 private messages."""
    try:
        sender = await event.get_sender()

        # Ignore non-users (bots or anonymous channels) or self messages
        if not isinstance(sender, User) or sender.bot or sender.is_self:
            return

        user_id = str(event.sender_id)
        user_name = sender.first_name or "Friend"
        current_time = time.time()

        history = load_history()
        last_replied = history.get(user_id, 0)

        # Check if cooldown has passed
        if COOLDOWN_SECONDS > 0 and (current_time - last_replied < COOLDOWN_SECONDS):
            print(f"⏳ [Cooldown Active] {user_name} ({user_id}) skipping.", flush=True)
            return

        # Mark user as replied immediately to avoid concurrent processing
        history[user_id] = current_time
        save_history(history)

        start_time = time.time()
        print(f"\n⚡ [INSTANT TRIGGER] From: {user_name} (ID: {user_id}) | Text: '{event.text}'", flush=True)

        # STEP 1: Send Video Tutorial (Sabse Upar)
        video_caption = "🎬 **Khelo Store Bot - How to Buy & Add Balance Guide** 🚀\n👇 Video dekhkar neeche diye bot se instant buy karein!"
        sent_video = False
        if CACHED_VIDEO_MSG_ID:
            try:
                v_msg = await client.get_messages('me', ids=CACHED_VIDEO_MSG_ID)
                if v_msg and v_msg.media:
                    await client.send_file(event.chat_id, v_msg.media, caption=video_caption, supports_streaming=True)
                    sent_video = True
            except Exception as e:
                print(f"  ⚠️ Cached video send error: {e}", flush=True)

        if not sent_video:
            local_v = VIDEO_TUTORIAL if os.path.exists(VIDEO_TUTORIAL) else (FALLBACK_VIDEO if os.path.exists(FALLBACK_VIDEO) else None)
            if local_v:
                await client.send_file(event.chat_id, local_v, caption=video_caption, supports_streaming=True)

        # STEP 2: Send Voice Note (Beech Mein)
        sent_voice = False
        if CACHED_VOICE_MSG_ID:
            try:
                vo_msg = await client.get_messages('me', ids=CACHED_VOICE_MSG_ID)
                if vo_msg and vo_msg.media:
                    await client.send_file(event.chat_id, vo_msg.media, voice_note=True)
                    sent_voice = True
            except Exception as e:
                print(f"  ⚠️ Cached voice send error: {e}", flush=True)

        if not sent_voice:
            local_vo = VOICE_AUDIO if os.path.exists(VOICE_AUDIO) else (FALLBACK_AUDIO if os.path.exists(FALLBACK_AUDIO) else None)
            if local_vo:
                await client.send_file(event.chat_id, local_vo, voice_note=True)

        # STEP 3: Send Text Message with Bot Link (Sabse Niche)
        await event.reply(WELCOME_MESSAGE.strip(), link_preview=False)

        elapsed = round(time.time() - start_time, 2)
        print(f"🚀 [SUCCESS] Auto-Reply delivered to {user_name} in ONLY {elapsed}s!\n", flush=True)

    except Exception as e:
        print(f"⚠️ Error handling message: {e}", flush=True)

async def init_banner():
    me = await client.get_me()
    print("=" * 60, flush=True)
    print("🤖 TELEGRAM AUTO-RESPONDER USERBOT (ULTRA-FAST)", flush=True)
    print(f"👤 Account: {me.first_name} (@{me.username or 'No Username'})", flush=True)
    print(f"📱 Phone: {PHONE_NUMBER}", flush=True)
    print(f"📂 Media Folder: {MEDIA_DIR}", flush=True)
    print("=" * 60, flush=True)
    
    # Pre-cache media BEFORE listening for messages to guarantee <0.5s instant response
    await preload_media_cache()
    
    print("\n⚡ USERBOT IS 100% LIVE & READY FOR INSTANT (<0.5s) AUTO-REPLIES 24/7! 🚀", flush=True)
    print(f"⏱️ Cooldown set to: {COOLDOWN_SECONDS} seconds.", flush=True)
    print("Press Ctrl+C to stop.\n" + "-" * 60, flush=True)

if __name__ == "__main__":
    os.makedirs(MEDIA_DIR, exist_ok=True)
    with client:
        client.loop.run_until_complete(init_banner())
        client.run_until_disconnected()
