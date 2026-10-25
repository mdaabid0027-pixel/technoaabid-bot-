@extends('admin.layout')

@section('title', 'In-Game Items & Posters')
@section('header_title', 'In-Game Items (Showcase & WhatsApp Buy)')

@section('styles')
<style>
    .showcase-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }

    .showcase-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
    }

    .showcase-header {
        padding: 16px 20px;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .showcase-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .preview-box {
        padding: 16px;
        background: #0f172a;
        color: #ffffff;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        border-bottom: 1px solid var(--border-color);
    }

    .preview-img {
        width: 100%;
        max-height: 180px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .preview-placeholder {
        width: 100%;
        height: 140px;
        background: #1e293b;
        border: 2px dashed #475569;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 13px;
        margin-bottom: 12px;
    }

    .showcase-body {
        padding: 20px;
        flex-grow: 1;
    }

    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 6px;
        display: block;
    }

    .input-field {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 14px;
        outline: none;
    }

    .input-field:focus {
        border-color: #3b82f6;
    }

    .btn-save {
        width: 100%;
        padding: 10px;
        background-color: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-save:hover {
        background-color: #1d4ed8;
    }

    .info-card {
        background: linear-gradient(135deg, #eff6ff, #f0fdf4);
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
</style>
@endsection

@section('content')
<div class="showcase-grid">
    @foreach($items as $id => $item)
        <div class="showcase-card">
            <div class="showcase-header">
                <h3>{{ $item['button_text'] }}</h3>
                <span class="badge" style="background: #e0f2fe; color: #0284c7; padding: 4px 8px; border-radius: 6px; font-size: 12px;">WhatsApp DM</span>
            </div>

            <!-- Live Telegram Preview Box -->
            <div class="preview-box">
                @if(!empty($item['photo_url']))
                    <img src="{{ asset(ltrim(parse_url($item['photo_url'], PHP_URL_PATH), '/')) }}" class="preview-img" alt="{{ $item['name'] }}">
                @elseif(!empty($item['photo_file_id']))
                    <div class="preview-placeholder" style="background: rgba(34, 197, 94, 0.1); border-color: #22c55e; color: #22c55e;">
                        <i class="fa-solid fa-cloud-check" style="font-size: 24px; margin-bottom: 6px;"></i>
                        <span>Telegram Cloud Photo Linked</span>
                        <code style="font-size: 10px; opacity: 0.7; max-width: 90%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $item['photo_file_id'] }}</code>
                    </div>
                @else
                    <div class="preview-placeholder">
                        <i class="fa-regular fa-image" style="font-size: 24px; margin-bottom: 6px;"></i>
                        <span>No Photo Uploaded</span>
                    </div>
                @endif

                <div style="font-size: 12px; font-weight: 600; color: #38bdf8; margin-bottom: 4px;">{{ $item['title'] }}</div>
                <div style="font-size: 11px; color: #94a3b8; white-space: pre-line; max-height: 48px; overflow: hidden;">{{ $item['caption'] }}</div>
                <div style="margin-top: 8px; padding: 6px 12px; background: #22c55e; color: #ffffff; border-radius: 6px; font-size: 11px; font-weight: 600;">
                    <i class="fa-brands fa-whatsapp"></i> Buy on WhatsApp (+91 {{ $item['whatsapp_number'] ?? 'XXXXXXXXXX' }})
                </div>
            </div>

            <!-- Edit Form -->
            <div class="showcase-body">
                <form action="{{ route('admin.ingame-items.update', $id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <label class="form-label">Upload Showcase Image</label>
                    <input type="file" name="image" class="input-field" accept="image/*">

                    <label class="form-label">Telegram Photo File ID (Optional)</label>
                    <input type="text" name="photo_file_id" class="input-field" value="{{ $item['photo_file_id'] ?? '' }}" placeholder="Paste Telegram file_id if any">

                    <label class="form-label">WhatsApp Number (For Direct Buy Link)</label>
                    <input type="text" name="whatsapp_number" class="input-field" value="{{ $item['whatsapp_number'] ?? 'XXXXXXXXXX' }}" placeholder="XXXXXXXXXX" required>

                    <label class="form-label">Showcase Title</label>
                    <input type="text" name="title" class="input-field" value="{{ $item['title'] ?? '' }}" required>

                    <label class="form-label">Caption / Description</label>
                    <textarea name="caption" class="input-field" rows="3" style="resize: vertical;" required>{{ $item['caption'] ?? '' }}</textarea>

                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Save {{ $item['name'] }}
                    </button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
