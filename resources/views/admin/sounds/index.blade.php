@extends('admin.layout')

@section('title', 'Sound Effects & Event Mappings')
@section('header_title', '🎵 Sound Effects & Event Manager')

@section('content')
<div class="sounds-container" style="max-width: 1280px; margin: 0 auto;">

    <!-- SECTION 1: EVENT-WISE SOUND ASSIGNMENT -->
    <div style="background: var(--bg-surface, #ffffff); border-radius: 18px; border: 1px solid var(--border-color, #e2e8f0); padding: 22px 24px; margin-bottom: 28px; box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
        
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 19px; font-weight: 800; color: var(--text-primary, #0f172a); margin: 0;">
                    🎯 Event Sound Settings
                </h3>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" form="eventMappingForm" class="btn btn-primary" style="background: #4f46e5; font-weight: 800; padding: 9px 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Sounds
                </button>
                <button type="button" onclick="document.getElementById('uploadModal').style.display='flex'" class="btn btn-secondary" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; font-weight: 700; padding: 9px 16px; border-radius: 10px;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Audio
                </button>
            </div>
        </div>

        <form id="eventMappingForm" action="{{ route('admin.sounds.save_events') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 16px;">
                @foreach($events as $eKey => $eData)
                    @php 
                        $currentSoundId = $eventMappings[$eKey] ?? $eData['default'];
                        $currentSound = collect($sounds)->firstWhere('id', $currentSoundId);
                        $isMuted = ($currentSoundId === 'none');
                    @endphp

                    <div style="background: #f8fafc; border: 1.5px solid {{ $isMuted ? '#e2e8f0' : $eData['color'] . '40' }}; border-radius: 12px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">
                        
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 34px; height: 34px; border-radius: 8px; background: {{ $eData['color'] }}15; color: {{ $eData['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                                    <i class="{{ $eData['icon'] }}"></i>
                                </div>
                                <h4 style="font-size: 14.5px; font-weight: 800; margin: 0; color: var(--text-primary, #0f172a);">
                                    {{ $eData['title'] }}
                                </h4>
                            </div>

                            @if($isMuted)
                                <span style="font-size: 10px; font-weight: 800; background: #fee2e2; color: #dc2626; padding: 2px 7px; border-radius: 6px;">
                                    MUTED
                                </span>
                            @else
                                <span style="font-size: 10px; font-weight: 800; background: {{ $eData['color'] }}20; color: {{ $eData['color'] }}; padding: 2px 7px; border-radius: 6px;">
                                    ACTIVE
                                </span>
                            @endif
                        </div>

                        <!-- Dropdown Selector & Quick Play -->
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <select name="sound_event_{{ $eKey }}" id="select_event_{{ $eKey }}" class="form-control" style="flex: 1; padding: 9px 12px; font-size: 13px; font-weight: 700; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; outline: none;" onchange="updateEventAudioPreview('{{ $eKey }}')">
                                <option value="none" {{ $currentSoundId === 'none' ? 'selected' : '' }}>🔇 Mute (No Voice Note)</option>
                                <optgroup label="Sounds">
                                    @foreach($sounds as $snd)
                                        <option value="{{ $snd['id'] }}" data-file="{{ asset('audio/' . $snd['file']) }}" {{ $currentSoundId === $snd['id'] ? 'selected' : '' }}>
                                            {{ $snd['emoji'] ?? '🎵' }} {{ $snd['title'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>

                            <button type="button" onclick="playSelectedEventSound('{{ $eKey }}')" id="btn_preview_{{ $eKey }}" title="Preview sound" style="width: 36px; height: 36px; border-radius: 8px; background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0;">
                                <i class="fa-solid fa-play"></i>
                            </button>
                        </div>

                    </div>
                @endforeach
            </div>
        </form>
    </div>

    <!-- SECTION 2: SOUNDBOARD AUDIO LIBRARY -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <div>
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #6366f1; margin-bottom: 4px;">
                🎧 Audio Jukebox
            </div>
            <h3 style="font-size: 20px; font-weight: 800; color: var(--text-primary, #0f172a); margin: 0;">
                All Available Meme Sounds & Music Library
            </h3>
        </div>
        <div style="font-size: 13px; color: var(--text-secondary, #64748b);">
            Total <strong>{{ count($sounds) }}</strong> Audio Clips Available
        </div>
    </div>

    <!-- Sound Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; margin-bottom: 40px;">
        @foreach($sounds as $s)
            @php
                // Check which events currently use this sound
                $assignedEvents = [];
                foreach($eventMappings as $evK => $evSnd) {
                    if ($evSnd === $s['id']) {
                        $assignedEvents[] = $events[$evK]['badge'] ?? $evK;
                    }
                }
            @endphp

            <div class="sound-card" style="background: var(--bg-surface, #ffffff); border-radius: 16px; border: {{ !empty($assignedEvents) ? '2px solid #6366f1' : '1px solid var(--border-color, #e2e8f0)' }}; padding: 22px; position: relative; box-shadow: {{ !empty($assignedEvents) ? '0 8px 20px -4px rgba(99, 102, 241, 0.2)' : '0 2px 6px rgba(0,0,0,0.03)' }}; transition: all 0.2s ease;">
                
                @if(!empty($assignedEvents))
                    <div style="position: absolute; top: 14px; right: 14px; display: flex; gap: 4px; flex-wrap: wrap;">
                        @foreach($assignedEvents as $evBadge)
                            <span style="background: #6366f1; color: #ffffff; font-size: 9.5px; font-weight: 800; padding: 3px 8px; border-radius: 20px; letter-spacing: 0.5px; text-transform: uppercase;">
                                ✓ {{ $evBadge }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: {{ !empty($assignedEvents) ? 'rgba(99, 102, 241, 0.12)' : 'rgba(241, 245, 249, 1)' }}; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                        {{ $s['emoji'] ?? '🎵' }}
                    </div>
                    <div style="padding-right: 50px;">
                        <h4 style="font-size: 15px; font-weight: 800; margin: 0; color: var(--text-primary, #0f172a); line-height: 1.3;">
                            {{ $s['title'] }}
                        </h4>
                    </div>
                </div>

                <!-- Audio Player Control -->
                <div style="background: rgba(248, 250, 252, 0.8); border: 1px solid var(--border-color, #e2e8f0); border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
                    <button type="button" onclick="toggleAudioPlay('{{ $s['id'] }}', '{{ asset('audio/' . $s['file']) }}')" id="playBtn_{{ $s['id'] }}" style="width: 38px; height: 38px; border-radius: 50%; background: #6366f1; color: #ffffff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);">
                        <i class="fa-solid fa-play" id="playIcon_{{ $s['id'] }}"></i>
                    </button>
                    
                    <div style="flex: 1; margin: 0 12px;">
                        <div style="font-size: 11.5px; font-weight: 700; color: var(--text-primary, #0f172a);" id="status_{{ $s['id'] }}">
                            Ready to Play
                        </div>
                        <div style="height: 4px; background: #e2e8f0; border-radius: 2px; margin-top: 4px; overflow: hidden;">
                            <div id="progress_{{ $s['id'] }}" style="width: 0%; height: 100%; background: #6366f1; transition: width 0.1s linear;"></div>
                        </div>
                    </div>

                    <span style="font-family: monospace; font-size: 11px; color: var(--text-secondary, #64748b); font-weight: 600;" id="timer_{{ $s['id'] }}">
                        0:00
                    </span>
                </div>

                <!-- 1-Click Quick Assign Dropdown & Test -->
                <div style="display: flex; gap: 8px;">
                    <div style="position: relative; flex: 1;">
                        <button type="button" onclick="toggleAssignMenu('{{ $s['id'] }}')" class="btn btn-primary" style="width: 100%; padding: 8px 12px; font-size: 12.5px; font-weight: 700; border-radius: 8px; background: #4f46e5; display: flex; align-items: center; justify-content: space-between;">
                            <span><i class="fa-solid fa-bolt"></i> Assign To...</span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 11px;"></i>
                        </button>
                        
                        <div id="assignMenu_{{ $s['id'] }}" style="display: none; position: absolute; bottom: 100%; left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2); z-index: 100; margin-bottom: 6px; overflow: hidden;">
                            <div style="padding: 6px 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; background: #f8fafc; border-bottom: 1px solid #f1f5f9;">
                                Choose Event to Assign:
                            </div>
                            @foreach($events as $evK => $evData)
                                <form action="{{ route('admin.sounds.assign_event') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="event_key" value="{{ $evK }}">
                                    <input type="hidden" name="sound_id" value="{{ $s['id'] }}">
                                    <button type="submit" style="width: 100%; text-align: left; padding: 9px 14px; font-size: 12.5px; font-weight: 700; background: none; border: none; border-bottom: 1px solid #f8fafc; cursor: pointer; color: #334155; display: flex; align-items: center; gap: 8px;">
                                        <i class="{{ $evData['icon'] }}" style="color: {{ $evData['color'] }}; width: 16px;"></i>
                                        <span>{{ $evData['title'] }}</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>

                    <form action="{{ route('admin.sounds.test_telegram') }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="sound_id" value="{{ $s['id'] }}">
                        <button type="submit" title="Send test audio to Telegram" style="padding: 8px 14px; font-size: 12.5px; font-weight: 700; border-radius: 8px; background: #f1f5f9; color: #0284c7; border: 1px solid #cbd5e1; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-brands fa-telegram"></i> Test
                        </button>
                    </form>
                </div>

            </div>
        @endforeach
    </div>

    <!-- Upload Custom Modal -->
    <div id="uploadModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;" onclick="if(event.target === this) document.getElementById('uploadModal').style.display='none'">
        <div style="background: #ffffff; width: 100%; max-width: 450px; border-radius: 8px; padding: 22px 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); border: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 16.5px; font-weight: 800; margin: 0; color: #0f172a;">
                    ☁️ Upload Custom Audio
                </h3>
                <button type="button" onclick="document.getElementById('uploadModal').style.display='none'" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #64748b;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('admin.sounds.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 5px;">
                        Sound Name / Title
                    </label>
                    <input type="text" name="sound_name" class="form-control" placeholder="e.g. My Custom Theme" style="width: 100%; padding: 9px 12px; font-size: 13px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none;" required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 5px;">
                        Audio File (.mp3, .m4a, .wav)
                    </label>
                    <input type="file" name="audio_file" accept="audio/*" class="form-control" style="width: 100%; padding: 7px 10px; font-size: 12.5px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none;" required>
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('uploadModal').style.display='none'" class="btn btn-secondary" style="padding: 8px 16px; border-radius: 6px; font-size: 13px;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" style="background: #4f46e5; padding: 8px 18px; border-radius: 6px; font-weight: 700; font-size: 13px;">
                        Upload Audio
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Audio Player Script -->
<script>
let currentAudio = null;
let currentPlayingId = null;

function toggleAudioPlay(soundId, url) {
    if (currentAudio && currentPlayingId === soundId) {
        if (!currentAudio.paused) {
            currentAudio.pause();
            const icon = document.getElementById('playIcon_' + soundId);
            if (icon) icon.className = 'fa-solid fa-play';
            const status = document.getElementById('status_' + soundId);
            if (status) status.innerText = 'Paused';
            return;
        } else {
            currentAudio.play();
            const icon = document.getElementById('playIcon_' + soundId);
            if (icon) icon.className = 'fa-solid fa-pause';
            const status = document.getElementById('status_' + soundId);
            if (status) status.innerText = 'Playing...';
            return;
        }
    }

    if (currentAudio) {
        currentAudio.pause();
        if (currentPlayingId) {
            const oldIcon = document.getElementById('playIcon_' + currentPlayingId);
            if (oldIcon) oldIcon.className = 'fa-solid fa-play';
            const oldStatus = document.getElementById('status_' + currentPlayingId);
            if (oldStatus) oldStatus.innerText = 'Ready to Play';
            const oldProg = document.getElementById('progress_' + currentPlayingId);
            if (oldProg) oldProg.style.width = '0%';
        }
    }

    currentAudio = new Audio(url);
    currentPlayingId = soundId;

    const icon = document.getElementById('playIcon_' + soundId);
    if (icon) icon.className = 'fa-solid fa-pause';
    const status = document.getElementById('status_' + soundId);
    if (status) status.innerText = 'Playing...';

    currentAudio.addEventListener('timeupdate', () => {
        if (currentAudio.duration) {
            const percent = (currentAudio.currentTime / currentAudio.duration) * 100;
            const prog = document.getElementById('progress_' + soundId);
            if (prog) prog.style.width = percent + '%';
            
            const mins = Math.floor(currentAudio.currentTime / 60);
            const secs = Math.floor(currentAudio.currentTime % 60);
            const timer = document.getElementById('timer_' + soundId);
            if (timer) timer.innerText = mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
    });

    currentAudio.addEventListener('ended', () => {
        const icon = document.getElementById('playIcon_' + soundId);
        if (icon) icon.className = 'fa-solid fa-play';
        const status = document.getElementById('status_' + soundId);
        if (status) status.innerText = 'Finished';
        const prog = document.getElementById('progress_' + soundId);
        if (prog) prog.style.width = '0%';
    });

    currentAudio.play().catch(e => {
        console.error('Playback error:', e);
        const status = document.getElementById('status_' + soundId);
        if (status) status.innerText = 'Error playing';
    });
}

function playSelectedEventSound(eventKey) {
    const select = document.getElementById('select_event_' + eventKey);
    if (!select) return;
    
    const selectedOption = select.options[select.selectedIndex];
    const fileUrl = selectedOption.getAttribute('data-file');
    
    if (fileUrl) {
        toggleAudioPlay('event_' + eventKey, fileUrl);
    } else {
        alert('This event is set to Mute (No sound).');
    }
}

function toggleAssignMenu(soundId) {
    // Close other menus first
    document.querySelectorAll('[id^="assignMenu_"]').forEach(el => {
        if (el.id !== 'assignMenu_' + soundId) {
            el.style.display = 'none';
        }
    });

    const menu = document.getElementById('assignMenu_' + soundId);
    if (menu) {
        menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
    }
}

// Close menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.sound-card')) {
        document.querySelectorAll('[id^="assignMenu_"]').forEach(el => {
            el.style.display = 'none';
        });
    }
});
</script>
@endsection
