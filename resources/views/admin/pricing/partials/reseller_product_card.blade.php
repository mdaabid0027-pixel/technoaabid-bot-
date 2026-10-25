@php
    $pName = strtolower($prod->name);
    $isBitAim = str_contains($pName, 'bitaim') || str_contains($pName, 'bit');
    $isCarrom = str_contains($pName, 'carrom');
    $isSoccer = str_contains($pName, 'soccer');
    $isSnake = str_contains($pName, 'snake') && !$isSoccer;
    $isKos = str_contains($pName, 'kos') || str_contains($pName, 'ks');
    $isAimAi = str_contains($pName, 'ai') || str_contains($pName, 'aim ai');

    $pId = $prod->id;
    $rPrices = $resellerPricesMap[$pId] ?? [];

    $fmt = function($val) {
        if ($val === null || $val === '' || (float)$val <= 0) return '';
        $f = (float)$val;
        return ($f == (int)$f) ? (string)((int)$f) : number_format($f, 2, '.', '');
    };

    // Helper to get standard rate for placeholder
    $getStd = function($resellerVal, $baseVal) use ($fmt) {
        $val = ((float)$resellerVal > 0) ? $resellerVal : $baseVal;
        return $fmt($val);
    };
@endphp

<div class="pricing-card" style="border-color: #cbd5e1;">
    <div class="pricing-card-header" style="background: #faf5ff; border-bottom: 1px solid #f3e8ff;">
        <div class="pricing-card-title">
            @if($isSoccer)
                <i class="fa-solid fa-futbol" style="color: #059669;"></i>
            @elseif($isBitAim)
                <i class="fa-solid fa-rocket" style="color: #ec4899;"></i>
            @elseif($isCarrom)
                <i class="fa-solid fa-bullseye" style="color: #ef4444;"></i>
            @elseif($isSnake)
                <i class="fa-solid fa-staff-snake" style="color: #f59e0b;"></i>
            @elseif($isKos)
                <i class="fa-solid fa-gamepad" style="color: #3b82f6;"></i>
            @elseif($isAimAi)
                <i class="fa-solid fa-robot" style="color: #8b5cf6;"></i>
            @else
                <i class="fa-solid fa-gamepad" style="color: #2563eb;"></i>
            @endif
            <span>{{ $prod->name }}</span>
        </div>
        <span style="font-size: 11px; font-weight: 700; background: #f3e8ff; color: #7e22ce; padding: 3px 8px; border-radius: 6px;">
            VIP Custom Override
        </span>
    </div>

    <div class="pricing-card-body">
        <form action="{{ route('admin.pricing.update_reseller_batch', $prod->id) }}" method="POST" onsubmit="saveResellerPricesAjax(event, this, '{{ addslashes($prod->name) }}')">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="telegram_user_id" class="reseller-id-field" value="{{ $selectedUser?->id }}">

            @if($isSoccer)
                <!-- SOCCER STAR: 3D, 10D, 30D -->
                <div class="plans-grid">
                    @php $has3d = isset($rPrices['3d']); $std3d = $getStd($prod->reseller_price_3d, $prod->price_3d); @endphp
                    <div class="plan-box" style="{{ $has3d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>3 Days (3D)</span>
                            <span style="font-size: 10px; color: {{ $has3d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has3d ? '⭐ VIP Custom' : "Std: ₹{$std3d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_3d" value="{{ $has3d ? $fmt($rPrices['3d']) : '' }}" class="plan-input" placeholder="{{ $std3d }}">
                        </div>
                    </div>

                    @php $has10d = isset($rPrices['10d']); $std10d = $getStd($prod->reseller_price_10d, $prod->price_10d); @endphp
                    <div class="plan-box" style="{{ $has10d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>10 Days (10D)</span>
                            <span style="font-size: 10px; color: {{ $has10d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has10d ? '⭐ VIP Custom' : "Std: ₹{$std10d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_10d" value="{{ $has10d ? $fmt($rPrices['10d']) : '' }}" class="plan-input" placeholder="{{ $std10d }}">
                        </div>
                    </div>

                    @php $has30d = isset($rPrices['30d']); $std30d = $getStd($prod->reseller_price_30d, $prod->price_30d); @endphp
                    <div class="plan-box" style="{{ $has30d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>30 Days (30D)</span>
                            <span style="font-size: 10px; color: {{ $has30d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has30d ? '⭐ VIP Custom' : "Std: ₹{$std30d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_30d" value="{{ $has30d ? $fmt($rPrices['30d']) : '' }}" class="plan-input" placeholder="{{ $std30d }}">
                        </div>
                    </div>
                </div>

            @elseif($isBitAim)
                <!-- BITAIM+: 7D, 30D, 90D, LIFETIME -->
                <div class="plans-grid">
                    @php $has7d = isset($rPrices['7d']); $std7d = $getStd($prod->reseller_price_7d, $prod->price_7d); @endphp
                    <div class="plan-box" style="{{ $has7d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>7 Days (1W)</span>
                            <span style="font-size: 10px; color: {{ $has7d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has7d ? '⭐ VIP Custom' : "Std: ₹{$std7d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_7d" value="{{ $has7d ? $fmt($rPrices['7d']) : '' }}" class="plan-input" placeholder="{{ $std7d }}">
                        </div>
                    </div>

                    @php $has30d = isset($rPrices['30d']); $std30d = $getStd($prod->reseller_price_30d, $prod->price_30d); @endphp
                    <div class="plan-box" style="{{ $has30d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>30 Days (1M)</span>
                            <span style="font-size: 10px; color: {{ $has30d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has30d ? '⭐ VIP Custom' : "Std: ₹{$std30d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_30d" value="{{ $has30d ? $fmt($rPrices['30d']) : '' }}" class="plan-input" placeholder="{{ $std30d }}">
                        </div>
                    </div>

                    @php $has90d = isset($rPrices['90d']); $std90d = $getStd($prod->reseller_price_90d, $prod->price_90d); @endphp
                    <div class="plan-box" style="{{ $has90d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>90 Days (3M)</span>
                            <span style="font-size: 10px; color: {{ $has90d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has90d ? '⭐ VIP Custom' : "Std: ₹{$std90d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_90d" value="{{ $has90d ? $fmt($rPrices['90d']) : '' }}" class="plan-input" placeholder="{{ $std90d }}">
                        </div>
                    </div>

                    @php $hasLt = isset($rPrices['lifetime']); $stdLt = $getStd($prod->reseller_price_lifetime, $prod->price_lifetime); @endphp
                    <div class="plan-box" style="{{ $hasLt ? 'background: #fdf2f8; border: 1.5px solid #ec4899;' : 'background: #fdf2f8; border: 1px solid #fbcfe8;' }}">
                        <div class="plan-box-label">
                            <span style="color: #be185d;">💎 Lifetime</span>
                            <span style="font-size: 10px; color: {{ $hasLt ? '#db2777' : '#94a3b8' }}; font-weight: 700;">
                                {{ $hasLt ? '⭐ VIP Custom' : "Std: ₹{$stdLt}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #db2777;">₹</span>
                            <input type="number" step="any" name="price_lifetime" value="{{ $hasLt ? $fmt($rPrices['lifetime']) : '' }}" class="plan-input" placeholder="{{ $stdLt }}">
                        </div>
                    </div>
                </div>

            @elseif($isCarrom)
                <!-- AIM CARROM KING: NORMAL MOD & AUTO QUEUE MOD -->
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #334155; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <span><i class="fa-solid fa-shield-halved" style="color: #3b82f6;"></i> Normal Mod VIP Rates</span>
                </div>
                <div class="plans-grid" style="margin-bottom: 14px;">
                    @php $has3d = isset($rPrices['3d']); $std3d = $getStd($prod->reseller_price_3d, $prod->price_3d); @endphp
                    <div class="plan-box" style="{{ $has3d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>3 Days (3D)</span>
                            <span style="font-size: 10px; color: {{ $has3d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has3d ? '⭐ VIP Custom' : "Std: ₹{$std3d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_3d" value="{{ $has3d ? $fmt($rPrices['3d']) : '' }}" class="plan-input" placeholder="{{ $std3d }}">
                        </div>
                    </div>

                    @php $has7d = isset($rPrices['7d']); $std7d = $getStd($prod->reseller_price_7d, $prod->price_7d); @endphp
                    <div class="plan-box" style="{{ $has7d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>7 Days (7D)</span>
                            <span style="font-size: 10px; color: {{ $has7d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has7d ? '⭐ VIP Custom' : "Std: ₹{$std7d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_7d" value="{{ $has7d ? $fmt($rPrices['7d']) : '' }}" class="plan-input" placeholder="{{ $std7d }}">
                        </div>
                    </div>

                    @php $has30d = isset($rPrices['30d']); $std30d = $getStd($prod->reseller_price_30d, $prod->price_30d); @endphp
                    <div class="plan-box" style="{{ $has30d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>30 Days (30D)</span>
                            <span style="font-size: 10px; color: {{ $has30d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has30d ? '⭐ VIP Custom' : "Std: ₹{$std30d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_30d" value="{{ $has30d ? $fmt($rPrices['30d']) : '' }}" class="plan-input" placeholder="{{ $std30d }}">
                        </div>
                    </div>

                    @php $has90d = isset($rPrices['90d']); $std90d = $getStd($prod->reseller_price_90d, $prod->price_90d); @endphp
                    <div class="plan-box" style="{{ $has90d ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                        <div class="plan-box-label">
                            <span>90 Days (90D)</span>
                            <span style="font-size: 10px; color: {{ $has90d ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                {{ $has90d ? '⭐ VIP Custom' : "Std: ₹{$std90d}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #7c3aed;">₹</span>
                            <input type="number" step="any" name="price_90d" value="{{ $has90d ? $fmt($rPrices['90d']) : '' }}" class="plan-input" placeholder="{{ $std90d }}">
                        </div>
                    </div>
                </div>

                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #7c3aed; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <span><i class="fa-solid fa-bolt" style="color: #7c3aed;"></i> Auto Queue Mod VIP Rates</span>
                </div>
                <div class="plans-grid">
                    @php $has3dq = isset($rPrices['3d_queue']); $std3dq = $getStd($prod->reseller_price_3d_queue, $prod->price_3d_queue ?: $prod->price_3d); @endphp
                    <div class="plan-box" style="{{ $has3dq ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #fdf4ff; border: 1px solid #f5d0fe;' }}">
                        <div class="plan-box-label">
                            <span style="color: #86198f;">3D Queue</span>
                            <span style="font-size: 10px; color: {{ $has3dq ? '#7c3aed' : '#a21caf' }}; font-weight: 700;">
                                {{ $has3dq ? '⭐ VIP Custom' : "Std: ₹{$std3dq}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #86198f;">₹</span>
                            <input type="number" step="any" name="price_3d_queue" value="{{ $has3dq ? $fmt($rPrices['3d_queue']) : '' }}" class="plan-input" placeholder="{{ $std3dq }}">
                        </div>
                    </div>

                    @php $has7dq = isset($rPrices['7d_queue']); $std7dq = $getStd($prod->reseller_price_7d_queue, $prod->price_7d_queue ?: $prod->price_7d); @endphp
                    <div class="plan-box" style="{{ $has7dq ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #fdf4ff; border: 1px solid #f5d0fe;' }}">
                        <div class="plan-box-label">
                            <span style="color: #86198f;">7D Queue</span>
                            <span style="font-size: 10px; color: {{ $has7dq ? '#7c3aed' : '#a21caf' }}; font-weight: 700;">
                                {{ $has7dq ? '⭐ VIP Custom' : "Std: ₹{$std7dq}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #86198f;">₹</span>
                            <input type="number" step="any" name="price_7d_queue" value="{{ $has7dq ? $fmt($rPrices['7d_queue']) : '' }}" class="plan-input" placeholder="{{ $std7dq }}">
                        </div>
                    </div>

                    @php $has30dq = isset($rPrices['30d_queue']); $std30dq = $getStd($prod->reseller_price_30d_queue, $prod->price_30d_queue ?: $prod->price_30d); @endphp
                    <div class="plan-box" style="{{ $has30dq ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #fdf4ff; border: 1px solid #f5d0fe;' }}">
                        <div class="plan-box-label">
                            <span style="color: #86198f;">30D Queue</span>
                            <span style="font-size: 10px; color: {{ $has30dq ? '#7c3aed' : '#a21caf' }}; font-weight: 700;">
                                {{ $has30dq ? '⭐ VIP Custom' : "Std: ₹{$std30dq}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #86198f;">₹</span>
                            <input type="number" step="any" name="price_30d_queue" value="{{ $has30dq ? $fmt($rPrices['30d_queue']) : '' }}" class="plan-input" placeholder="{{ $std30dq }}">
                        </div>
                    </div>

                    @php $has90dq = isset($rPrices['90d_queue']); $std90dq = $getStd($prod->reseller_price_90d_queue, $prod->price_90d_queue ?: $prod->price_90d); @endphp
                    <div class="plan-box" style="{{ $has90dq ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #fdf4ff; border: 1px solid #f5d0fe;' }}">
                        <div class="plan-box-label">
                            <span style="color: #86198f;">90D Queue</span>
                            <span style="font-size: 10px; color: {{ $has90dq ? '#7c3aed' : '#a21caf' }}; font-weight: 700;">
                                {{ $has90dq ? '⭐ VIP Custom' : "Std: ₹{$std90dq}" }}
                            </span>
                        </div>
                        <div class="plan-input-wrap">
                            <span class="plan-currency" style="color: #86198f;">₹</span>
                            <input type="number" step="any" name="price_90d_queue" value="{{ $has90dq ? $fmt($rPrices['90d_queue']) : '' }}" class="plan-input" placeholder="{{ $std90dq }}">
                        </div>
                    </div>
                </div>

            @elseif($isKos)
                <!-- KOS: 1D, 7D, 15D, 30D -->
                <div class="plans-grid">
                    @foreach(['1d' => '1 Day (1D)', '7d' => '7 Days (7D)', '15d' => '15 Days (15D)', '30d' => '30 Days (30D)'] as $k => $label)
                        @php 
                            $hasK = isset($rPrices[$k]); 
                            $rField = "reseller_price_{$k}";
                            $bField = "price_{$k}";
                            $stdVal = $getStd($prod->$rField, $prod->$bField);
                        @endphp
                        <div class="plan-box" style="{{ $hasK ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                            <div class="plan-box-label">
                                <span>{{ $label }}</span>
                                <span style="font-size: 10px; color: {{ $hasK ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                    {{ $hasK ? '⭐ VIP Custom' : "Std: ₹{$stdVal}" }}
                                </span>
                            </div>
                            <div class="plan-input-wrap">
                                <span class="plan-currency" style="color: #7c3aed;">₹</span>
                                <input type="number" step="any" name="price_{{ $k }}" value="{{ $hasK ? $fmt($rPrices[$k]) : '' }}" class="plan-input" placeholder="{{ $stdVal }}">
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif($isSnake)
                <!-- SNAKE: 3D, 10D, 30D, 90D -->
                <div class="plans-grid">
                    @foreach(['3d' => '3 Days (3D)', '10d' => '10 Days (10D)', '30d' => '30 Days (30D)', '90d' => '90 Days (90D)'] as $k => $label)
                        @php 
                            $hasK = isset($rPrices[$k]); 
                            $rField = "reseller_price_{$k}";
                            $bField = "price_{$k}";
                            $stdVal = $getStd($prod->$rField, $prod->$bField);
                        @endphp
                        <div class="plan-box" style="{{ $hasK ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                            <div class="plan-box-label">
                                <span>{{ $label }}</span>
                                <span style="font-size: 10px; color: {{ $hasK ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                    {{ $hasK ? '⭐ VIP Custom' : "Std: ₹{$stdVal}" }}
                                </span>
                            </div>
                            <div class="plan-input-wrap">
                                <span class="plan-currency" style="color: #7c3aed;">₹</span>
                                <input type="number" step="any" name="price_{{ $k }}" value="{{ $hasK ? $fmt($rPrices[$k]) : '' }}" class="plan-input" placeholder="{{ $stdVal }}">
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif($isAimAi)
                <!-- AIM AI: 1D, 3D, 7D, 15D, 30D, 90D -->
                <div class="plans-grid">
                    @foreach(['1d' => '1 Day (1D)', '3d' => '3 Days (3D)', '7d' => '7 Days (7D)', '15d' => '15 Days (15D)', '30d' => '30 Days (30D)', '90d' => '90 Days (90D)'] as $k => $label)
                        @php 
                            $hasK = isset($rPrices[$k]); 
                            $rField = "reseller_price_{$k}";
                            $bField = "price_{$k}";
                            $stdVal = $getStd($prod->$rField, $prod->$bField);
                        @endphp
                        <div class="plan-box" style="{{ $hasK ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                            <div class="plan-box-label">
                                <span>{{ $label }}</span>
                                <span style="font-size: 10px; color: {{ $hasK ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                    {{ $hasK ? '⭐ VIP Custom' : "Std: ₹{$stdVal}" }}
                                </span>
                            </div>
                            <div class="plan-input-wrap">
                                <span class="plan-currency" style="color: #7c3aed;">₹</span>
                                <input type="number" step="any" name="price_{{ $k }}" value="{{ $hasK ? $fmt($rPrices[$k]) : '' }}" class="plan-input" placeholder="{{ $stdVal }}">
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                <!-- GENERIC -->
                <div class="plans-grid">
                    @foreach(['1d' => '1 Day (1D)', '7d' => '7 Days (7D)', '30d' => '30 Days (30D)'] as $k => $label)
                        @php 
                            $hasK = isset($rPrices[$k]); 
                            $rField = "reseller_price_{$k}";
                            $bField = "price_{$k}";
                            $stdVal = $getStd($prod->$rField, $prod->$bField);
                        @endphp
                        <div class="plan-box" style="{{ $hasK ? 'background: #faf5ff; border: 1.5px solid #a855f7;' : 'background: #f8fafc; border: 1px solid #e2e8f0;' }}">
                            <div class="plan-box-label">
                                <span>{{ $label }}</span>
                                <span style="font-size: 10px; color: {{ $hasK ? '#7c3aed' : '#94a3b8' }}; font-weight: 700;">
                                    {{ $hasK ? '⭐ VIP Custom' : "Std: ₹{$stdVal}" }}
                                </span>
                            </div>
                            <div class="plan-input-wrap">
                                <span class="plan-currency" style="color: #7c3aed;">₹</span>
                                <input type="number" step="any" name="price_{{ $k }}" value="{{ $hasK ? $fmt($rPrices[$k]) : '' }}" class="plan-input" placeholder="{{ $stdVal }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <button type="submit" class="btn" style="width: 100%; margin-top: 14px; background: #7c3aed; color: #ffffff; font-weight: 700; padding: 10px 16px; border-radius: 8px; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; border: none; transition: all 0.2s;">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save VIP Custom Prices</span>
            </button>
        </form>
    </div>
</div>
