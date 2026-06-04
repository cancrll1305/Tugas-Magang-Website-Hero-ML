@php use Illuminate\Support\Str; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLBB Hero Hub</title>

    <style>
        :root{
            --bg:#050505;
            --bg-soft:#0d0d0f;
            --panel:#121215;
            --panel-2:#1a1a1f;
            --line:#2f2f37;
            --text:#f7f3eb;
            --muted:#a7a297;
            --orange:#ff7a00;
            --orange-soft:#ffb15c;
            --orange-deep:#d85a00;
            --shadow:0 22px 60px rgba(0,0,0,.42);
        }

        *{box-sizing:border-box}

        body{
            margin:0;
            color:var(--text);
            font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255,122,0,.24), transparent 30%),
                radial-gradient(circle at top right, rgba(255,122,0,.12), transparent 26%),
                linear-gradient(180deg, #0a0a0b 0%, #050505 50%, #0a0a0b 100%);
            min-height:100vh;
        }

        a{
            color:inherit;
            text-decoration:none;
        }

        .shell{
            width:min(1280px, calc(100% - 32px));
            margin:0 auto;
            padding:28px 0 48px;
        }

        .hero-bar{
            display:grid;
            grid-template-columns:1.2fr .8fr;
            gap:24px;
            margin-bottom:28px;
        }

        .hero-bar-card,
        .meta-card,
        .filters,
        .hero-card{
            border:1px solid rgba(255,255,255,.08);
            background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
            box-shadow:var(--shadow);
        }

        .hero-bar-card{
            position:relative;
            overflow:hidden;
            border-radius:28px;
            padding:34px;
            min-height:280px;
            background:
                linear-gradient(130deg, rgba(255,122,0,.18), rgba(255,122,0,0) 42%),
                linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.02)),
                #0d0d0f;
        }

        .hero-bar-card::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(rgba(255,255,255,.02) 1px, transparent 1px);
            background-size:22px 22px;
            mask-image:linear-gradient(180deg, rgba(0,0,0,.85), transparent);
            pointer-events:none;
        }

        .eyebrow{
            display:inline-flex;
            align-items:center;
            gap:10px;
            padding:8px 14px;
            border-radius:999px;
            letter-spacing:.18em;
            text-transform:uppercase;
            font-size:11px;
            font-weight:700;
            color:var(--orange-soft);
            background:rgba(255,122,0,.09);
            border:1px solid rgba(255,122,0,.3);
        }

        .title{
            margin:18px 0 14px;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:clamp(42px, 6vw, 78px);
            line-height:.95;
            letter-spacing:.02em;
            text-transform:uppercase;
        }

        .subtitle{
            max-width:760px;
            color:#ddd6ca;
            font-size:16px;
            line-height:1.75;
        }

        .stats-strip{
            display:grid;
            grid-template-columns:repeat(3, minmax(0,1fr));
            gap:14px;
            margin-top:28px;
        }

        .stat-chip{
            border-radius:20px;
            padding:16px 18px;
            background:rgba(0,0,0,.38);
            border:1px solid rgba(255,255,255,.06);
        }

        .stat-chip strong{
            display:block;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:28px;
            color:var(--orange-soft);
        }

        .stat-chip span{
            display:block;
            margin-top:6px;
            color:var(--muted);
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:.14em;
        }

        .meta-card{
            border-radius:28px;
            padding:24px;
            background:
                radial-gradient(circle at top right, rgba(255,122,0,.14), transparent 38%),
                #111114;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            gap:18px;
        }

        .meta-card h2{
            margin:0 0 10px;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            text-transform:uppercase;
            letter-spacing:.08em;
            font-size:24px;
        }

        .meta-card p{
            margin:0;
            color:#cbc5bb;
            line-height:1.7;
        }

        .meta-list{
            display:grid;
            gap:14px;
        }

        .meta-item{
            display:flex;
            justify-content:space-between;
            gap:20px;
            padding-bottom:14px;
            border-bottom:1px solid rgba(255,255,255,.08);
            font-size:14px;
        }

        .meta-item span{
            color:var(--muted);
            text-transform:uppercase;
            letter-spacing:.12em;
            font-size:11px;
        }

        .meta-item strong{
            text-align:right;
            color:var(--text);
            font-weight:700;
        }

        .filters{
            border-radius:24px;
            padding:22px;
            margin-bottom:24px;
            background:#0f0f12;
        }

        .filters-top{
            display:flex;
            justify-content:space-between;
            gap:18px;
            align-items:flex-start;
            margin-bottom:18px;
        }

        .filters-top h3{
            margin:0;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:22px;
            text-transform:uppercase;
            letter-spacing:.08em;
        }

        .filters-top p{
            margin:8px 0 0;
            color:var(--muted);
            line-height:1.6;
        }

        .search-form{
            display:flex;
            gap:12px;
            align-items:center;
            flex-wrap:wrap;
        }

        .search-form input{
            width:min(340px, 100%);
            padding:14px 16px;
            border-radius:16px;
            border:1px solid rgba(255,255,255,.08);
            background:#08080a;
            color:var(--text);
            outline:none;
        }

        .search-form input:focus{
            border-color:rgba(255,122,0,.55);
            box-shadow:0 0 0 4px rgba(255,122,0,.12);
        }

        .button{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:14px 18px;
            border-radius:16px;
            border:1px solid rgba(255,122,0,.34);
            background:linear-gradient(180deg, var(--orange), var(--orange-deep));
            color:#130a05;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.08em;
            cursor:pointer;
        }

        .button-secondary{
            background:#151519;
            color:var(--text);
            border-color:rgba(255,255,255,.08);
        }

        .role-pills{
            display:flex;
            flex-wrap:wrap;
            gap:10px;
        }

        .role-pill{
            padding:10px 16px;
            border-radius:999px;
            border:1px solid rgba(255,255,255,.08);
            background:#141418;
            color:#ddd6ca;
            font-size:13px;
            transition:.2s ease;
        }

        .role-pill:hover{
            border-color:rgba(255,122,0,.34);
            color:var(--orange-soft);
        }

        .role-pill.active{
            background:linear-gradient(180deg, rgba(255,122,0,.2), rgba(255,122,0,.08));
            border-color:rgba(255,122,0,.5);
            color:#fff4e9;
        }

        .results-head{
            display:flex;
            justify-content:space-between;
            gap:16px;
            align-items:center;
            margin-bottom:18px;
        }

        .results-head h4{
            margin:0;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:20px;
            text-transform:uppercase;
            letter-spacing:.08em;
        }

        .results-head p{
            margin:0;
            color:var(--muted);
        }

        .hero-grid{
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(245px, 1fr));
            gap:20px;
        }

        .hero-card{
            display:flex;
            flex-direction:column;
            border-radius:22px;
            overflow:hidden;
            background:#0f0f12;
            transition:transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }

        .hero-card:hover{
            transform:translateY(-6px);
            border-color:rgba(255,122,0,.34);
            box-shadow:0 26px 44px rgba(0,0,0,.48), 0 0 0 1px rgba(255,122,0,.14) inset;
        }

        .hero-media{
            position:relative;
            aspect-ratio:4/4.8;
            overflow:hidden;
            background:linear-gradient(180deg, rgba(255,122,0,.08), transparent), #17171b;
        }

        .hero-media img{
            width:100%;
            height:100%;
            object-fit:cover;
            transition:transform .35s ease;
        }

        .hero-card:hover .hero-media img{
            transform:scale(1.04);
        }

        .role-badge{
            position:absolute;
            top:14px;
            left:14px;
            padding:8px 12px;
            border-radius:999px;
            background:rgba(0,0,0,.66);
            border:1px solid rgba(255,122,0,.34);
            color:var(--orange-soft);
            font-size:11px;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.1em;
        }

        .hero-body{
            display:flex;
            flex-direction:column;
            gap:12px;
            padding:18px;
        }

        .hero-topline{
            display:flex;
            justify-content:space-between;
            gap:14px;
            align-items:flex-start;
        }

        .hero-name{
            margin:0;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:27px;
            text-transform:uppercase;
            line-height:1;
        }

        .hero-lane{
            color:var(--orange-soft);
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:.1em;
            text-align:right;
        }

        .hero-specialty{
            color:#d7d0c4;
            font-weight:600;
        }

        .hero-excerpt{
            color:var(--muted);
            line-height:1.65;
            font-size:14px;
            min-height:68px;
        }

        .hero-meta{
            display:flex;
            flex-wrap:wrap;
            gap:8px;
        }

        .mini-chip{
            padding:8px 10px;
            border-radius:999px;
            font-size:12px;
            border:1px solid rgba(255,255,255,.07);
            background:#17171b;
            color:#ddd6ca;
        }

        .empty-state{
            border-radius:24px;
            padding:48px 28px;
            text-align:center;
            background:#0f0f12;
            border:1px dashed rgba(255,122,0,.26);
            color:#d3cdc1;
        }

        .empty-state h5{
            margin:0 0 10px;
            font-size:22px;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            text-transform:uppercase;
            letter-spacing:.08em;
        }

        @media (max-width: 980px){
            .hero-bar{
                grid-template-columns:1fr;
            }
        }

        @media (max-width: 720px){
            .shell{
                width:min(100% - 20px, 1280px);
                padding-top:16px;
            }

            .hero-bar-card,
            .meta-card,
            .filters{
                border-radius:22px;
            }

            .hero-bar-card{
                padding:24px 20px;
                min-height:auto;
            }

            .title{
                font-size:42px;
            }

            .stats-strip{
                grid-template-columns:1fr;
            }

            .filters-top,
            .results-head{
                flex-direction:column;
            }

            .search-form input{
                width:100%;
            }
        }
    </style>
</head>
<body>

<div class="shell">
    <section class="hero-bar">
        <div class="hero-bar-card">
            <div class="eyebrow">MLBB Esports Scout Report</div>
            <h1 class="title">Hero Command Center</h1>
            <p class="subtitle">
                Pantau seluruh hero Mobile Legends dari satu dashboard yang lebih rapi: role, lane, specialty,
                lore, ability kit, dan media gallery sudah disusun ulang dari hasil scraping terbaru.
            </p>

            <div class="stats-strip">
                <div class="stat-chip">
                    <strong>{{ $heroCount }}</strong>
                    <span>Visible Heroes</span>
                </div>
                <div class="stat-chip">
                    <strong>{{ $roles->count() }}</strong>
                    <span>Tracked Roles</span>
                </div>
                <div class="stat-chip">
                    <strong>{{ $totalHeroCount }}</strong>
                    <span>Total Data Pool</span>
                </div>
            </div>
        </div>

        <aside class="meta-card">
            <div>
                <h2>Broadcast Notes</h2>
                <p>
                    Tema halaman ini diarahkan seperti portal media esports: kontras hitam-oranye, card lebih tegas,
                    dan struktur konten langsung mengikuti data hasil scraping yang baru.
                </p>
            </div>

            <div class="meta-list">
                <div class="meta-item">
                    <span>Active Role</span>
                    <strong>{{ $currentRole ?: 'All Roles' }}</strong>
                </div>
                <div class="meta-item">
                    <span>Search Query</span>
                    <strong>{{ $currentQuery !== '' ? $currentQuery : 'None' }}</strong>
                </div>
                <div class="meta-item">
                    <span>Data Source</span>
                    <strong>Latest Scraper Output</strong>
                </div>
            </div>
        </aside>
    </section>

    <section class="filters">
        <div class="filters-top">
            <div>
                <h3>Filter Desk</h3>
                <p>Pilih role atau cari hero tertentu untuk mempercepat review roster.</p>
            </div>

            <form class="search-form" method="GET" action="{{ url('/heroes') }}">
                @if($currentRole)
                    <input type="hidden" name="role" value="{{ $currentRole }}">
                @endif
                <input
                    type="text"
                    name="q"
                    placeholder="Search hero, specialty, lane, or summary..."
                    value="{{ $currentQuery }}"
                >
                <button class="button" type="submit">Search</button>
                @if($currentRole || $currentQuery)
                    <a class="button button-secondary" href="{{ url('/heroes') }}">Reset</a>
                @endif
            </form>
        </div>

        <div class="role-pills">
            <a href="{{ url('/heroes') }}{{ $currentQuery !== '' ? '?q=' . urlencode($currentQuery) : '' }}"
               class="role-pill {{ !$currentRole ? 'active' : '' }}">
                All Roles
            </a>

            @foreach($roles as $role)
                <a href="{{ url('/heroes') . '?role=' . urlencode($role) . ($currentQuery !== '' ? '&q=' . urlencode($currentQuery) : '') }}"
                   class="role-pill {{ $currentRole === $role ? 'active' : '' }}">
                    {{ $role }}
                </a>
            @endforeach
        </div>
    </section>

    <section>
        <div class="results-head">
            <div>
                <h4>Hero Lineup</h4>
                <p>{{ $heroCount }} hero ditampilkan{{ $currentRole ? ' untuk role ' . $currentRole : '' }}.</p>
            </div>
        </div>

        @if($heroes->isEmpty())
            <div class="empty-state">
                <h5>No Hero Found</h5>
                <p>Coba ubah role filter atau kata pencarian Anda.</p>
            </div>
        @else
            <div class="hero-grid">
                @foreach($heroes as $hero)
                    @php
                        $role = $hero['info']['Role'] ?? 'Unknown Role';
                        $lane = $hero['info']['Lane Recc.'] ?? 'No Lane';
                        $specialty = $hero['info']['Specialty'] ?? 'No Specialty';
                        $resource = $hero['info']['Skill resource'] ?? null;
                        $damageType = $hero['info']['Damage type'] ?? null;
                        $excerpt = $hero['lead']['summary'][0] ?? ($hero['story']['intro'][0] ?? 'No summary available.');
                        $title = $hero['title'] ?? $hero['name'];
                    @endphp

                    <a class="hero-card" href="{{ url('/heroes/' . urlencode($hero['name'])) }}">
                        <div class="hero-media">
                            <img
                                src="{{ url('/hero-image?url=' . urlencode($hero['icon'])) }}"
                                alt="{{ $hero['name'] }}"
                                loading="lazy"
                                decoding="async"
                            >
                            <div class="role-badge">{{ $role }}</div>
                        </div>

                        <div class="hero-body">
                            <div class="hero-topline">
                                <div>
                                    <h2 class="hero-name">{{ $hero['name'] }}</h2>
                                    <div class="hero-specialty">{{ $specialty }}</div>
                                </div>

                                <div class="hero-lane">{{ $lane }}</div>
                            </div>

                            <div class="hero-excerpt">{{ Str::limit($excerpt, 120) }}</div>

                            <div class="hero-meta">
                                <span class="mini-chip">{{ Str::limit($title, 30) }}</span>
                                @if($resource)
                                    <span class="mini-chip">{{ $resource }}</span>
                                @endif
                                @if($damageType)
                                    <span class="mini-chip">{{ $damageType }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>

</body>
</html>
