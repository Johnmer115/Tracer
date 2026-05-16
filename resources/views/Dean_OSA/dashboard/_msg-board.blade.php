{{-- ══════════════════════════════════════════════
     MESSAGE / REMARKS BOARD PARTIAL
     Usage: @include('Dean_OSA.dashboard._msg-board', ['canCompose' => true/false])
══════════════════════════════════════════════ --}}
@php $canCompose = $canCompose ?? false; @endphp

<div class="msg-board">
    <div class="msg-board-header">
        <div class="msg-board-title">
            <i class="fas fa-comment-dots"></i> Remarks Board
        </div>
        @if($canCompose)
            <button type="button" class="btn btn-add" onclick="openComposeModal()">
                <i class="fas fa-plus"></i> New Remark
            </button>
        @endif
    </div>

    <div class="msg-board-body">
        @forelse($messages as $msg)
            @php
                $typeConfig = match($msg->type) {
                    'announcement' => ['icon' => 'fa-bullhorn',    'color' => '#dc2626', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'label' => 'Announcement'],
                    'reminder'     => ['icon' => 'fa-bell',        'color' => '#d97706', 'bg' => '#fffbeb', 'border' => '#fcd34d', 'label' => 'Reminder'],
                    default        => ['icon' => 'fa-comment-alt', 'color' => '#014ea8', 'bg' => '#f0f6ff', 'border' => '#93c5fd', 'label' => 'General'],
                };
            @endphp
            <div class="msg-card {{ $msg->is_pinned ? 'msg-pinned' : '' }}">
                <div class="msg-accent" style="background:{{ $typeConfig['color'] }};"></div>
                <div class="msg-content">
                    <div class="msg-meta">
                        <span class="msg-type-badge" style="background:{{ $typeConfig['bg'] }}; color:{{ $typeConfig['color'] }}; border:1px solid {{ $typeConfig['border'] }};">
                            <i class="fas {{ $typeConfig['icon'] }}" style="font-size:9px;"></i>
                            {{ $typeConfig['label'] }}
                        </span>
                        @if($msg->is_pinned)
                            <span class="msg-pin-badge">
                                <i class="fas fa-thumbtack"></i> Pinned
                            </span>
                        @endif
                        <span class="msg-time">
                            <i class="fas fa-clock" style="font-size:9px;"></i>
                            {{ $msg->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="msg-text">{!! nl2br(e($msg->message)) !!}</div>
                    <div class="msg-footer">
                        <div class="msg-author">
                            <div class="msg-avatar">{{ strtoupper(substr($msg->account->username ?? '?', 0, 1)) }}</div>
                            <span class="msg-author-name">{{ $msg->account->username ?? 'Unknown' }}</span>
                            <span class="msg-author-role">{{ $msg->account->usertype ?? '' }}</span>
                        </div>
                        @if($canCompose)
                            <div class="msg-actions">
                                <form action="{{ route('dean_osa.messages.pin', $msg->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="msg-action-btn" title="{{ $msg->is_pinned ? 'Unpin' : 'Pin' }}">
                                        <i class="fas fa-thumbtack" style="{{ $msg->is_pinned ? 'color:var(--primary);' : '' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('dean_osa.messages.delete', $msg->id) }}" method="POST" style="display:inline;"
                                    onsubmit="return confirm('Delete this message?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="msg-action-btn msg-action-del" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="msg-empty">
                <i class="fas fa-comment-slash"></i>
                <div>No remarks yet.{{ $canCompose ? ' Click <strong>New Remark</strong> to post one.' : '' }}</div>
            </div>
        @endforelse
    </div>
</div>
