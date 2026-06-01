@php
    $messageRoutePrefix = $messageRoutePrefix ?? request()->route()?->getName();
    $messageRoutePrefix = \Illuminate\Support\Str::before($messageRoutePrefix, '.');
    $canComposeMessages = $canComposeMessages ?? false;
    $canManageMessages = $canManageMessages ?? false;
    $messageBranches = $messageBranches ?? collect();
@endphp

<div class="msg-board">
    <div class="msg-board-header">
        <div class="msg-board-title">
            <i class="fas fa-comment-dots"></i> Remarks Board
        </div>
        @if($canComposeMessages)
            <button type="button" class="btn btn-add" onclick="openComposeModal()">
                <i class="fas fa-plus"></i> New Remark
            </button>
        @endif
    </div>

    <div class="msg-board-body">
        @forelse($messages as $msg)
            @php
                $typeConfig = match($msg->type) {
                    'announcement' => ['icon' => 'fa-bullhorn', 'color' => '#dc2626', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'label' => 'Announcement'],
                    'reminder' => ['icon' => 'fa-bell', 'color' => '#d97706', 'bg' => '#fffbeb', 'border' => '#fcd34d', 'label' => 'Reminder'],
                    default => ['icon' => 'fa-comment-alt', 'color' => '#014ea8', 'bg' => '#f0f6ff', 'border' => '#93c5fd', 'label' => 'General'],
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
                            <span class="msg-pin-badge"><i class="fas fa-thumbtack"></i> Pinned</span>
                        @endif
                        <span class="msg-time"><i class="fas fa-clock" style="font-size:9px;"></i> {{ $msg->created_at->diffForHumans() }}</span>
                        <span class="msg-time">
                            <i class="fas fa-location-dot" style="font-size:9px;"></i>
                            {{ $msg->branch?->name ?? 'All branches' }}
                        </span>
                    </div>

                    <div class="msg-text">{!! nl2br(e($msg->message)) !!}</div>

                    <div class="msg-footer">
                        <div class="msg-author">
                            <div class="msg-avatar">{{ strtoupper(substr($msg->account->username ?? '?', 0, 1)) }}</div>
                            <span class="msg-author-name">{{ $msg->account->username ?? 'Unknown' }}</span>
                            <span class="msg-author-role">{{ $msg->account->usertype ?? '' }}</span>
                        </div>
                        @if($canManageMessages)
                            <div class="msg-actions">
                                <form action="{{ route($messageRoutePrefix . '.messages.pin', $msg->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="msg-action-btn" title="{{ $msg->is_pinned ? 'Unpin' : 'Pin' }}">
                                        <i class="fas fa-thumbtack" style="{{ $msg->is_pinned ? 'color:var(--primary);' : '' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route($messageRoutePrefix . '.messages.delete', $msg->id) }}" method="POST" style="display:inline;"
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
                <div>No remarks yet.</div>
            </div>
        @endforelse
    </div>
</div>

@if($canComposeMessages)
    <div class="compose-overlay" id="composeOverlay" onclick="closeComposeModal()">
        <div class="compose-modal" onclick="event.stopPropagation()">
            <div class="compose-header">
                <div class="compose-header-icon"><i class="fas fa-pen"></i></div>
                <div>
                    <h3 class="compose-title">New Remark</h3>
                    <p class="compose-subtitle">Post a message to the dashboard board</p>
                </div>
                <button type="button" class="compose-close" onclick="closeComposeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route($messageRoutePrefix . '.messages.store') }}" method="POST">
                @csrf
                <div class="compose-body">
                    <div style="margin-bottom:16px;">
                        <label class="compose-label">Type</label>
                        <div class="compose-type-group">
                            @foreach(['general' => ['General', '#f0f6ff', '#014ea8', '#93c5fd', 'fa-comment-alt'], 'announcement' => ['Announcement', '#fef2f2', '#dc2626', '#fca5a5', 'fa-bullhorn'], 'reminder' => ['Reminder', '#fffbeb', '#d97706', '#fcd34d', 'fa-bell']] as $type => [$label, $bg, $color, $border, $icon])
                                <label class="compose-type-option">
                                    <input type="radio" name="type" value="{{ $type }}" @checked($type === 'general')>
                                    <span class="compose-type-chip" style="--chip-bg:{{ $bg }}; --chip-color:{{ $color }}; --chip-border:{{ $border }};">
                                        <i class="fas {{ $icon }}"></i> {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label class="compose-label" for="messageBranch">Target Branch</label>
                        <select name="branch_id" id="messageBranch" class="form-control searchable-select">
                            <option value="">All branches</option>
                            @foreach($messageBranches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="compose-label" for="composeMsg">Message</label>
                        <textarea name="message" id="composeMsg" class="form-control"
                            rows="4" maxlength="2000" required
                            placeholder="Write your remark here..."
                            style="resize:vertical; border-radius:10px; font-size:13px;"></textarea>
                        <div style="text-align:right; font-size:10.5px; color:#94a3b8; margin-top:4px;">
                            <span id="charCount">0</span>/2000
                        </div>
                    </div>
                </div>

                <div class="compose-footer">
                    <button type="button" class="btn btn-filter" onclick="closeComposeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-add">
                        <i class="fas fa-paper-plane"></i> Post Remark
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
