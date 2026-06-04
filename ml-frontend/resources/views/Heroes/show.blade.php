@php use Illuminate\Support\Str; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $hero['name'] ?? 'Hero Detail' }} | MLBB Hero Hub</title>

    <style>
        :root{
            --bg:#040404;
            --bg-soft:#0b0b0d;
            --panel:#111114;
            --panel-2:#19191d;
            --line:#2d2d34;
            --text:#f7f3eb;
            --muted:#a5a092;
            --orange:#ff7a00;
            --orange-soft:#ffbf75;
            --orange-deep:#d75d00;
            --success:#76dba4;
            --shadow:0 28px 64px rgba(0,0,0,.46);
        }

        *{box-sizing:border-box}

        body{
            margin:0;
            color:var(--text);
            font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255,122,0,.2), transparent 28%),
                radial-gradient(circle at right center, rgba(255,122,0,.11), transparent 24%),
                linear-gradient(180deg, #0a0a0b 0%, #040404 48%, #09090b 100%);
        }

        a{
            color:inherit;
            text-decoration:none;
        }

        .page{
            width:min(1320px, calc(100% - 32px));
            margin:0 auto;
            padding:24px 0 56px;
        }

        .back-link{
            display:inline-flex;
            align-items:center;
            gap:10px;
            margin-bottom:18px;
            padding:12px 16px;
            border-radius:999px;
            background:rgba(255,122,0,.08);
            border:1px solid rgba(255,122,0,.28);
            color:var(--orange-soft);
            font-weight:700;
            letter-spacing:.04em;
        }

        .panel{
            background:linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.02));
            border:1px solid rgba(255,255,255,.08);
            box-shadow:var(--shadow);
            border-radius:28px;
        }

        .hero-main{
            overflow:hidden;
            background:
                linear-gradient(135deg, rgba(255,122,0,.16), transparent 36%),
                #0d0d10;
            margin-bottom:24px;
        }

        .hero-top{
            display:grid;
            grid-template-columns:340px minmax(0,1fr);
            gap:26px;
            padding:28px;
        }

        .hero-media{
            position:relative;
            overflow:hidden;
            border-radius:24px;
            background:
                radial-gradient(circle at top, rgba(255,122,0,.18), transparent 40%),
                #17171b;
            border:1px solid rgba(255,255,255,.06);
            min-height:430px;
        }

        .hero-media img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .hero-content{
            display:flex;
            flex-direction:column;
            gap:18px;
        }

        .eyebrow{
            display:inline-flex;
            align-items:center;
            gap:10px;
            width:max-content;
            padding:8px 14px;
            border-radius:999px;
            background:rgba(255,122,0,.08);
            border:1px solid rgba(255,122,0,.26);
            color:var(--orange-soft);
            text-transform:uppercase;
            letter-spacing:.16em;
            font-size:11px;
            font-weight:800;
        }

        .hero-title{
            margin:0;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:clamp(42px, 6vw, 74px);
            line-height:.92;
            text-transform:uppercase;
            letter-spacing:.03em;
        }

        .hero-subtitle{
            color:var(--orange-soft);
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            letter-spacing:.12em;
            text-transform:uppercase;
            font-size:13px;
        }

        .summary{
            color:#ddd6ca;
            line-height:1.8;
            font-size:16px;
        }

        .quote{
            margin:0;
            padding:18px 20px;
            border-left:4px solid var(--orange);
            border-radius:18px;
            background:rgba(255,255,255,.03);
            color:#f8e7d5;
            font-size:18px;
            line-height:1.7;
        }

        .meta-grid{
            display:grid;
            grid-template-columns:repeat(4, minmax(0, 1fr));
            gap:14px;
        }

        .meta-box{
            padding:16px;
            border-radius:18px;
            background:#131318;
            border:1px solid rgba(255,255,255,.06);
        }

        .meta-box span{
            display:block;
            color:var(--muted);
            font-size:11px;
            letter-spacing:.14em;
            text-transform:uppercase;
            margin-bottom:8px;
        }

        .meta-box strong{
            display:block;
            font-size:16px;
            line-height:1.5;
        }

        .hero-sidebar{
            display:grid;
            gap:22px;
            margin-bottom:22px;
        }

        .side-card{
            padding:22px;
            background:#0d0d10;
            border-color:rgba(255,255,255,.08);
        }

        .side-card h2{
            margin:0 0 14px;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            text-transform:uppercase;
            letter-spacing:.08em;
            font-size:22px;
        }

        .side-list{
            display:grid;
            gap:14px;
        }

        .side-item{
            display:grid;
            grid-template-columns:108px minmax(0,1fr);
            align-items:start;
            gap:18px;
            padding-bottom:14px;
            border-bottom:1px solid rgba(255,255,255,.08);
        }

        .side-item:last-child{
            padding-bottom:0;
            border-bottom:0;
        }

        .side-item span{
            color:var(--muted);
            font-size:11px;
            letter-spacing:.14em;
            text-transform:uppercase;
        }

        .side-item strong{
            text-align:left;
            font-size:14px;
            line-height:1.6;
            word-break:break-word;
        }

        .tag-row{
            display:flex;
            flex-wrap:wrap;
            gap:10px;
        }

        .tag{
            padding:9px 12px;
            border-radius:999px;
            background:#16161a;
            border:1px solid rgba(255,255,255,.07);
            font-size:12px;
            color:#dfd8cd;
        }

        .tag.emphasis{
            border-color:rgba(255,122,0,.38);
            color:var(--orange-soft);
            background:rgba(255,122,0,.08);
        }

        .layout{
            margin-top:24px;
        }

        .content{
            display:grid;
            gap:22px;
            min-width:0;
        }

        .hero-sidebar + .content{
            margin-top:0;
        }

        .section{
            padding:24px;
        }

        .section-head{
            display:flex;
            justify-content:space-between;
            gap:18px;
            align-items:flex-end;
            margin-bottom:18px;
        }

        .section-head h2{
            margin:0;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:28px;
            text-transform:uppercase;
            letter-spacing:.08em;
        }

        .section-head p{
            margin:0;
            color:var(--muted);
            line-height:1.6;
        }

        .info-grid{
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));
            gap:14px;
        }

        .info-card{
            border-radius:18px;
            padding:16px;
            background:#121216;
            border:1px solid rgba(255,255,255,.06);
        }

        .info-card span{
            display:block;
            color:var(--muted);
            text-transform:uppercase;
            letter-spacing:.12em;
            font-size:11px;
            margin-bottom:8px;
        }

        .info-card strong{
            display:block;
            font-size:15px;
            line-height:1.65;
        }

        .table-wrap{
            overflow:auto;
            border-radius:20px;
            border:1px solid rgba(255,255,255,.06);
            background:#101014;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th, td{
            padding:14px 16px;
            border-bottom:1px solid rgba(255,255,255,.06);
            text-align:left;
            vertical-align:top;
        }

        th{
            background:#17171c;
            color:#f5ebdf;
            text-transform:uppercase;
            font-size:12px;
            letter-spacing:.12em;
        }

        td{
            color:#ddd6ca;
        }

        tr:last-child td{
            border-bottom:0;
        }

        .growth{
            color:var(--success);
            font-weight:700;
        }

        .skill-stack{
            display:grid;
            gap:18px;
        }

        .skill-card{
            border-radius:24px;
            padding:22px;
            background:
                linear-gradient(140deg, rgba(255,122,0,.08), transparent 28%),
                #101014;
            border:1px solid rgba(255,255,255,.07);
        }

        .skill-header{
            display:grid;
            grid-template-columns:84px minmax(0,1fr);
            gap:18px;
            align-items:start;
        }

        .skill-icon{
            width:84px;
            height:84px;
            object-fit:cover;
            border-radius:20px;
            border:1px solid rgba(255,255,255,.08);
            background:#1c1c20;
        }

        .skill-title{
            margin:0;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:28px;
            line-height:1;
            text-transform:uppercase;
            letter-spacing:.05em;
        }

        .skill-subtitle{
            margin-top:8px;
            color:var(--orange-soft);
            text-transform:uppercase;
            letter-spacing:.12em;
            font-size:12px;
            font-weight:800;
        }

        .skill-description{
            margin-top:16px;
            color:#e2dbcf;
            line-height:1.9;
        }

        .skill-description p{
            margin:0 0 12px;
        }

        .skill-meta,
        .skill-tags,
        .icon-variants{
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            margin-top:14px;
        }

        .skill-chip{
            padding:9px 12px;
            border-radius:999px;
            background:#17171b;
            border:1px solid rgba(255,255,255,.07);
            font-size:12px;
            color:#dfd8cd;
        }

        .skill-variant-switcher{
            display:flex;
            flex-wrap:wrap;
            gap:10px;
            margin-top:14px;
        }

        .skill-variant-button{
            border:1px solid rgba(255,255,255,.09);
            background:#141419;
            color:#ddd6ca;
            border-radius:999px;
            padding:10px 14px;
            font:inherit;
            font-size:12px;
            letter-spacing:.02em;
            cursor:pointer;
            transition:background .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
        }

        .skill-variant-button:hover{
            transform:translateY(-1px);
            border-color:rgba(255,122,0,.45);
        }

        .skill-variant-button.is-active{
            background:linear-gradient(135deg, rgba(255,122,0,.22), rgba(215,93,0,.14));
            border-color:rgba(255,122,0,.55);
            color:#fff1de;
        }

        .skill-variant-panel{
            border-radius:18px;
            padding:16px;
            background:#141419;
            border:1px solid rgba(255,255,255,.06);
            margin-top:14px;
            display:none;
        }

        .skill-variant-panel.is-active{
            display:block;
        }

        .variant-skill-top{
            display:flex;
            gap:14px;
            align-items:flex-start;
        }

        .variant-skill-icon{
            width:62px;
            height:62px;
            object-fit:cover;
            border-radius:16px;
            border:1px solid rgba(255,255,255,.08);
            background:#1b1b20;
            flex:0 0 auto;
        }

        .skill-variant-panel h4{
            margin:0;
            font-size:16px;
            line-height:1.4;
            color:#fff1de;
        }

        .variant-skill-copy{
            margin-top:12px;
            color:#ddd6ca;
            line-height:1.8;
        }

        .icon-variants img{
            width:58px;
            height:58px;
            border-radius:14px;
            object-fit:cover;
            border:1px solid rgba(255,255,255,.08);
            background:#151519;
        }

        .variant-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
            gap:14px;
            margin-top:14px;
        }

        .variant-card{
            border-radius:18px;
            padding:14px;
            background:#141419;
            border:1px solid rgba(255,255,255,.06);
        }

        .variant-card h4{
            margin:0 0 12px;
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:.14em;
            color:var(--orange-soft);
        }

        .subblock{
            margin-top:18px;
            padding-top:18px;
            border-top:1px solid rgba(255,255,255,.08);
        }

        .subblock h3{
            margin:0 0 12px;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:18px;
            text-transform:uppercase;
            letter-spacing:.08em;
        }

        .terms{
            display:grid;
            gap:12px;
        }

        .term{
            border-radius:16px;
            padding:14px 16px;
            background:#141419;
            border:1px solid rgba(255,255,255,.06);
        }

        .term strong{
            display:block;
            margin-bottom:8px;
            color:var(--orange-soft);
        }

        .story-copy{
            display:grid;
            gap:14px;
            color:#dfd8cd;
            line-height:1.9;
        }

        .story-copy p{
            margin:0;
        }

        .story-columns{
            display:grid;
            grid-template-columns:repeat(2, minmax(0,1fr));
            gap:18px;
            margin-top:18px;
        }

        .story-panel{
            border-radius:20px;
            padding:18px;
            background:#111116;
            border:1px solid rgba(255,255,255,.06);
        }

        .story-panel h3{
            margin:0 0 12px;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:20px;
            text-transform:uppercase;
            letter-spacing:.08em;
        }

        .media-groups{
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
            gap:18px;
        }

        .media-group{
            border-radius:22px;
            padding:18px;
            background:#101014;
            border:1px solid rgba(255,255,255,.06);
        }

        .media-group h3{
            margin:0 0 14px;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:22px;
            text-transform:uppercase;
            letter-spacing:.08em;
        }

        .gallery-groups{
            display:grid;
            gap:18px;
        }

        .gallery-bucket{
            border-radius:22px;
            padding:18px;
            background:#101014;
            border:1px solid rgba(255,255,255,.06);
        }

        .gallery-bucket h3{
            margin:0;
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:24px;
            text-transform:uppercase;
            letter-spacing:.08em;
        }

        .gallery-subgroups{
            display:grid;
            gap:14px;
            margin-top:16px;
        }

        .gallery-subgroup{
            border-radius:18px;
            padding:16px;
            background:#0d0d10;
            border:1px solid rgba(255,255,255,.06);
        }

        .gallery-subgroup-title{
            margin:0 0 12px;
            color:var(--orange-soft);
            font-family:Bahnschrift, "Arial Narrow", sans-serif;
            font-size:18px;
            text-transform:uppercase;
            letter-spacing:.08em;
        }

        .gallery-list{
            display:flex;
            flex-wrap:wrap;
            gap:18px;
            align-items:stretch;
            width:100%;
            max-width:100%;
        }

        .gallery-list.is-skin .gallery-card{
            flex:1 1 240px;
            max-width:240px;
        }

        .gallery-list.is-avatar .gallery-card{
            flex:0 1 128px;
            max-width:128px;
        }

        .gallery-list.is-emote .gallery-card{
            flex:0 1 170px;
            max-width:170px;
        }

        .gallery-card{
            overflow:hidden;
            border-radius:18px;
            background:#17171b;
            border:1px solid rgba(255,255,255,.06);
            display:flex;
            flex-direction:column;
            min-height:100%;
        }

        .gallery-card img{
            width:100%;
            aspect-ratio:1/1;
            object-fit:cover;
            background:#111114;
        }

        .gallery-card.portrait img,
        .gallery-card.wide img{
            aspect-ratio:4/3;
        }

        .gallery-list.is-skin .gallery-card img{
            aspect-ratio:16/10;
        }

        .gallery-list.is-emote .gallery-card{
            background:#121217;
            border-radius:16px;
        }

        .gallery-list.is-emote .gallery-card img{
            object-fit:contain;
            aspect-ratio:1/1;
            max-height:132px;
            padding:16px;
            background:transparent;
        }

        .gallery-list.is-avatar .gallery-card{
            border-radius:16px;
        }

        .gallery-list.is-avatar .gallery-card img{
            aspect-ratio:1/1;
            max-height:110px;
        }

        .gallery-card figcaption{
            padding:12px;
            color:#e7dfd2;
            font-size:13px;
            line-height:1.5;
            text-align:center;
            word-break:break-word;
            min-height:64px;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .gallery-list.is-avatar .gallery-card figcaption,
        .gallery-list.is-emote .gallery-card figcaption{
            padding:10px 12px 14px;
            font-size:12px;
            line-height:1.45;
            min-height:72px;
        }

        .list-clean{
            display:grid;
            gap:12px;
            margin:0;
            padding-left:18px;
            color:#e0d9cd;
            line-height:1.75;
        }

        .list-clean li{
            padding-bottom:8px;
            border-bottom:1px solid rgba(255,255,255,.06);
        }

        .list-clean li:last-child{
            padding-bottom:0;
            border-bottom:0;
        }

        .links-list{
            display:grid;
            gap:10px;
        }

        .link-card{
            display:grid;
            gap:8px;
            padding:14px 16px;
            border-radius:16px;
            background:#141418;
            border:1px solid rgba(255,255,255,.06);
        }

        .link-card span{
            color:var(--orange-soft);
            font-weight:700;
        }

        .link-card small{
            color:var(--muted);
            word-break:break-all;
            line-height:1.5;
        }

        .links-footer{
            margin-top:24px;
        }

        .links-footer .side-card{
            width:100%;
            background:#0d0d10;
        }

        @media (max-width: 1180px){
            .layout{
                margin-top:24px;
            }
        }

        @media (max-width: 860px){
            .hero-top{
                grid-template-columns:1fr;
                padding:20px;
            }

            .hero-media{
                min-height:360px;
            }

            .meta-grid{
                grid-template-columns:repeat(2, minmax(0,1fr));
            }

            .story-columns{
                grid-template-columns:1fr;
            }
        }

        @media (max-width: 680px){
            .page{
                width:min(100% - 18px, 1320px);
                padding-top:16px;
            }

            .panel,
            .side-card{
                border-radius:22px;
            }

            .section{
                padding:20px;
            }

            .meta-grid,
            .info-grid{
                grid-template-columns:1fr;
            }

            .skill-header{
                grid-template-columns:1fr;
            }

            .skill-icon{
                width:74px;
                height:74px;
            }
        }
    </style>
</head>
<body>

@php
    $heroInfo = $hero['info'] ?? [];
    $lead = $hero['lead'] ?? [];
    $stats = $hero['stats'] ?? [];
    $skills = collect($hero['skills'] ?? [])->filter(function ($skill) {
        return !Str::contains(strtolower($skill['type'] ?? ''), 'resource')
            && !Str::contains(strtolower($skill['name'] ?? ''), 'resource');
    })->values();
    $story = $hero['story'] ?? [];
    $gallery = $hero['gallery'] ?? [];
    $relationships = $hero['relationships'] ?? [];
    $voicedBy = $hero['voicedBy'] ?? [];
    $links = collect($hero['links'] ?? [])->filter(fn ($link) => !empty($link['label']) && !empty($link['url']))->values();
    $summaryItems = $lead['summary'] ?? [];
    $storyIntro = $story['intro'] ?? [];
    $storyBio = $story['bio'] ?? [];
    $storySide = $story['sideStory'] ?? [];
    $heroTitle = $hero['title'] ?? $hero['name'];
    $selectedInfo = [
        'Role',
        'Specialty',
        'Lane Recc.',
        'Damage type',
        'Skill resource',
        'Basic attack type',
        'Price',
        'Release date',
    ];
    $topInfo = collect($selectedInfo)->mapWithKeys(function ($key) use ($heroInfo) {
        return isset($heroInfo[$key]) ? [$key => $heroInfo[$key]] : [];
    })->all();
    $profileInfo = collect($heroInfo)->except(array_merge($selectedInfo, array_keys($voicedBy)))->all();
    $sanitize = function ($text) {
        return trim(preg_replace('/\s{2,}/', ' ', (string) $text));
    };
    $isVariantTag = function ($url) {
        $path = urldecode(parse_url((string) $url, PHP_URL_PATH) ?? '');
        $filename = basename($path);

        return Str::contains($filename, ['_Tag.', '_Skin_Tag.', 'Skin_Tag.']);
    };
    $variantLabel = function ($url) {
        $path = urldecode(parse_url((string) $url, PHP_URL_PATH) ?? '');
        $filename = pathinfo(basename($path), PATHINFO_FILENAME);
        $label = str_replace('_', ' ', $filename);
        $label = preg_replace('/\s*Skin Tag$/i', '', $label);
        $label = preg_replace('/\s*Tag$/i', '', $label);

        return trim($label ?: 'Variant');
    };
    $galleryBuckets = [
        'Skins' => [],
        'Avatar' => [],
        'Emotes' => [],
        'Gallery Extras' => [],
    ];

    foreach ($gallery as $groupName => $items) {
        if (empty($items)) {
            continue;
        }

        $normalizedGroup = strtolower((string) $groupName);
        $bucketName = 'Gallery Extras';

        if (Str::contains($normalizedGroup, ['splash', 'painted', 'skin', 'artwork'])) {
            $bucketName = 'Skins';
        } elseif (Str::contains($normalizedGroup, ['avatar', 'icon'])) {
            $bucketName = 'Avatar';
        } elseif (Str::contains($normalizedGroup, ['emote', 'battle', 'sticker', 'spray'])) {
            $bucketName = 'Emotes';
        }

        $galleryBuckets[$bucketName][$groupName] = $items;
    }

    $galleryBuckets = collect($galleryBuckets)->filter(fn ($groups) => !empty($groups))->all();
@endphp

<div class="page">
    <a class="back-link" href="{{ url('/heroes') }}">← Back to Hero Hub</a>

    <article class="panel hero-main">
            <div class="hero-top">
                <div class="hero-media">
                    @if(!empty($hero['icon']))
                        <img
                            src="{{ url('/hero-image?url=' . urlencode($hero['icon'])) }}"
                            alt="{{ $hero['name'] }}"
                            decoding="async"
                        >
                    @endif
                </div>

                <div class="hero-content">
                    <div class="eyebrow">MLBB Feature Profile</div>
                    <div class="hero-subtitle">{{ $heroInfo['Role'] ?? 'Hero Dossier' }}</div>
                    <h1 class="hero-title">{{ $hero['name'] }}</h1>
                    <div class="summary">{{ $sanitize($heroTitle) }}</div>

                    @if(!empty($lead['quote']))
                        <blockquote class="quote">{{ $sanitize($lead['quote']) }}</blockquote>
                    @endif

                    @if(!empty($summaryItems))
                        <div class="summary">
                            @foreach($summaryItems as $item)
                                <div>{{ $sanitize($item) }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($topInfo))
                        <div class="meta-grid">
                            @foreach($topInfo as $label => $value)
                                <div class="meta-box">
                                    <span>{{ $label }}</span>
                                    <strong>{{ $sanitize($value) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
    </article>

    <section class="layout">
        <aside class="hero-sidebar">
            <div class="panel side-card">
                <h2>Hero Snapshot</h2>
                <div class="side-list">
                    @foreach([
                        'Alias' => $heroInfo['Alias'] ?? null,
                        'Species' => $heroInfo['Species'] ?? null,
                        'Occupation' => $heroInfo['Occupation'] ?? null,
                        'Birthday' => $heroInfo['Birthday'] ?? null,
                        'Born' => $heroInfo['Born'] ?? null,
                    ] as $label => $value)
                        @if($value)
                            <div class="side-item">
                                <span>{{ $label }}</span>
                                <strong>{{ $sanitize($value) }}</strong>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            @if(!empty($relationships))
                <div class="panel side-card">
                    <h2>Relationships</h2>
                    <ul class="list-clean">
                        @foreach($relationships as $relationship)
                            <li>{{ $sanitize($relationship) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($voicedBy))
                <div class="panel side-card">
                    <h2>Voice Cast</h2>
                    <div class="side-list">
                        @foreach($voicedBy as $language => $actor)
                            <div class="side-item">
                                <span>{{ $language }}</span>
                                <strong>{{ $sanitize($actor) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>

        <main class="content">

            @if(!empty($profileInfo))
                <article class="panel section">
                    <div class="section-head">
                        <div>
                            <h2>Hero Data Sheet</h2>
                            <p>Ringkasan informasi inti hasil scraping dari infobox utama dan lore panel.</p>
                        </div>
                    </div>

                    <div class="info-grid">
                        @foreach($profileInfo as $label => $value)
                            <div class="info-card">
                                <span>{{ $label }}</span>
                                <strong>{{ $sanitize($value) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endif

            @if(!empty($hero['infobox']['main']['sections']))
                <article class="panel section">
                    <div class="section-head">
                        <div>
                            <h2>Main Panels</h2>
                            <p>Kelompok data yang dipertahankan sesuai struktur infobox utama.</p>
                        </div>
                    </div>

                    <div class="media-groups">
                        @foreach($hero['infobox']['main']['sections'] as $section)
                            @php
                                $items = collect($section['items'] ?? [])->filter(fn ($item) => !empty($item['label']) && !empty($item['value']))->values();
                            @endphp

                            @if($items->isNotEmpty())
                                <div class="media-group">
                                    <h3>{{ $sanitize($section['title'] ?? 'Section') }}</h3>
                                    <div class="info-grid">
                                        @foreach($items as $item)
                                            <div class="info-card">
                                                <span>{{ $sanitize($item['label']) }}</span>
                                                <strong>{{ $sanitize($item['value']) }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </article>
            @endif

            @if(!empty($stats))
                <article class="panel section">
                    <div class="section-head">
                        <div>
                            <h2>Base Stats</h2>
                            <p>Tabel stat dasar hero seperti yang tampil pada halaman utama fandom.</p>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Level 1</th>
                                    <th>Level 15</th>
                                    <th>Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats as $stat)
                                    <tr>
                                        <td>{{ $sanitize($stat['attribute'] ?? '-') }}</td>
                                        <td>{{ $sanitize($stat['level1'] ?? '-') }}</td>
                                        <td>{{ $sanitize($stat['level15'] ?? '-') }}</td>
                                        <td class="growth">+{{ $sanitize($stat['growth'] ?? '-') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif

            @if($skills->isNotEmpty())
                <article class="panel section">
                    <div class="section-head">
                        <div>
                            <h2>Ability Loadout</h2>
                            <p>Detail skill, atribut tambahan, scaling table, term, dan notes hasil parsing terbaru.</p>
                        </div>
                    </div>

                    <div class="skill-stack">
                        @foreach($skills as $skill)
                            @php
                                $descriptionLines = collect(explode("\n", $skill['description'] ?? ''))
                                    ->map(fn ($line) => $sanitize($line))
                                    ->filter()
                                    ->values();
                                $tables = collect($skill['tables'] ?? [])->filter(function ($table) {
                                    return !empty($table['headers']) || !empty($table['rows']);
                                })->values();
                                $variants = collect($skill['variants'] ?? [])->filter(function ($variant) {
                                    return !empty($variant['name']);
                                })->values();
                            @endphp

                            <div class="skill-card">
                                <div class="skill-header">
                                    @if(!empty($skill['icon']))
                                        <img
                                            class="skill-icon"
                                            src="{{ url('/hero-image?url=' . urlencode($skill['icon'])) }}"
                                            alt="{{ $skill['name'] }}"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @endif

                                    <div>
                                        <h3 class="skill-title">{{ $sanitize($skill['name'] ?? '-') }}</h3>
                                        <div class="skill-subtitle">{{ $sanitize($skill['type'] ?? '-') }}</div>

                                        @if(!empty($skill['tags']))
                                            <div class="skill-tags">
                                                @foreach($skill['tags'] as $tag)
                                                    <span class="skill-chip emphasis">{{ $sanitize($tag) }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(!empty($skill['metadata']))
                                            <div class="skill-meta">
                                                @foreach($skill['metadata'] as $meta)
                                                    <span class="skill-chip">{{ $sanitize($meta) }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($descriptionLines->isNotEmpty())
                                    <div class="skill-description">
                                        @foreach($descriptionLines as $line)
                                            <p>{{ $line }}</p>
                                        @endforeach
                                    </div>
                                @endif

                                @if($variants->count() > 1)
                                    @php
                                        $skillVariantKey = Str::slug(($skill['name'] ?? 'skill') . '-' . $loop->index);
                                    @endphp

                                    <div class="subblock">
                                        <h3>Skill Variants</h3>
                                        <p>Pilih satu variant untuk melihat detail skill yang sesuai.</p>

                                        <div class="skill-variant-switcher" data-variant-group="{{ $skillVariantKey }}">
                                            @foreach($variants as $variant)
                                                @php
                                                    $panelId = $skillVariantKey . '-' . $loop->index;
                                                    $buttonLabel = $sanitize($variant['variantLabel'] ?? $variant['name'] ?? ('Variant ' . $loop->iteration));
                                                @endphp

                                                <button
                                                    type="button"
                                                    class="skill-variant-button {{ $loop->first ? 'is-active' : '' }}"
                                                    data-variant-target="{{ $panelId }}"
                                                >
                                                    {{ $buttonLabel }}
                                                </button>
                                            @endforeach
                                        </div>

                                        @foreach($variants as $variant)
                                            @php
                                                $panelId = $skillVariantKey . '-' . $loop->index;
                                                $variantLines = collect(explode("\n", $variant['description'] ?? ''))
                                                    ->map(fn ($line) => $sanitize($line))
                                                    ->filter()
                                                    ->values();
                                                $variantTables = collect($variant['tables'] ?? [])->filter(function ($table) {
                                                    return !empty($table['headers']) || !empty($table['rows']);
                                                })->values();
                                            @endphp

                                            <div id="{{ $panelId }}" class="skill-variant-panel {{ $loop->first ? 'is-active' : '' }}">
                                                <div class="variant-skill-top">
                                                    @if(!empty($variant['icon']))
                                                        <img
                                                            class="variant-skill-icon"
                                                            src="{{ url('/hero-image?url=' . urlencode($variant['icon'])) }}"
                                                            alt="{{ $variant['name'] }}"
                                                            loading="lazy"
                                                            decoding="async"
                                                        >
                                                    @endif

                                                    <div>
                                                        <div class="skill-tags" style="margin-top:0;">
                                                            @if(!empty($variant['variantLabel']))
                                                                <span class="skill-chip emphasis">{{ $sanitize($variant['variantLabel']) }}</span>
                                                            @endif
                                                            @foreach($variant['tags'] ?? [] as $tag)
                                                                <span class="skill-chip">{{ $sanitize($tag) }}</span>
                                                            @endforeach
                                                        </div>
                                                        <h4>{{ $sanitize($variant['name']) }}</h4>
                                                        @if(!empty($variant['metadata']))
                                                            <div class="skill-meta">
                                                                @foreach($variant['metadata'] as $meta)
                                                                    <span class="skill-chip">{{ $sanitize($meta) }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if($variantLines->isNotEmpty())
                                                    <div class="variant-skill-copy">
                                                        @foreach($variantLines as $line)
                                                            <p>{{ $line }}</p>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if($variantTables->isNotEmpty())
                                                    @foreach($variantTables as $table)
                                                        <div class="subblock">
                                                            <h3>{{ $sanitize($table['title'] ?? 'Variant Table') }}</h3>
                                                            <div class="table-wrap">
                                                                <table>
                                                                    @if(!empty($table['headers']))
                                                                        <thead>
                                                                            <tr>
                                                                                @foreach($table['headers'] as $header)
                                                                                    <th>{{ $sanitize($header) }}</th>
                                                                                @endforeach
                                                                            </tr>
                                                                        </thead>
                                                                    @endif
                                                                    <tbody>
                                                                        @foreach($table['rows'] ?? [] as $row)
                                                                            <tr>
                                                                                @php
                                                                                    $headerCount = count($table['headers'] ?? []);
                                                                                    $rowCount = count($row);
                                                                                @endphp

                                                                                @if($rowCount === 2 && $headerCount > 2)
                                                                                    <td>{{ $sanitize($row[0]) }}</td>
                                                                                    <td colspan="{{ $headerCount - 1 }}">{{ $sanitize($row[1]) }}</td>
                                                                                @else
                                                                                    @foreach($row as $cell)
                                                                                        <td>{{ $sanitize($cell) }}</td>
                                                                                    @endforeach
                                                                                @endif
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if(!empty($skill['iconVariants']))
                                    @php
                                        $variantGroups = [];
                                        $currentVariantLabel = 'Variant';

                                        foreach (($skill['iconVariants'] ?? []) as $variantUrl) {
                                            if ($isVariantTag($variantUrl)) {
                                                $currentVariantLabel = $variantLabel($variantUrl);

                                                if (!isset($variantGroups[$currentVariantLabel])) {
                                                    $variantGroups[$currentVariantLabel] = [];
                                                }

                                                continue;
                                            }

                                            if (!isset($variantGroups[$currentVariantLabel])) {
                                                $variantGroups[$currentVariantLabel] = [];
                                            }

                                            $variantGroups[$currentVariantLabel][] = $variantUrl;
                                        }

                                        $variantGroups = collect($variantGroups)
                                            ->filter(fn ($items) => !empty($items))
                                            ->all();
                                    @endphp

                                @if(!empty($variantGroups))
                                    <div class="subblock">
                                        <h3>Icon Variants</h3>
                                        <div class="variant-grid">
                                            @foreach($variantGroups as $variantName => $variantItems)
                                                <div class="variant-card">
                                                    <h4>{{ $sanitize($variantName) }}</h4>
                                                    <div class="icon-variants">
                                                        @foreach($variantItems as $variant)
                                                            <img
                                                                src="{{ url('/hero-image?url=' . urlencode($variant)) }}"
                                                                alt="{{ $sanitize($variantName) }} icon variant"
                                                                loading="lazy"
                                                                decoding="async"
                                                            >
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @endif

                                @if($tables->isNotEmpty())
                                    @foreach($tables as $table)
                                        <div class="subblock">
                                            <h3>{{ $sanitize($table['title'] ?? 'Skill Table') }}</h3>
                                            <div class="table-wrap">
                                                <table>
                                                    @if(!empty($table['headers']))
                                                        <thead>
                                                            <tr>
                                                                @foreach($table['headers'] as $header)
                                                                    <th>{{ $sanitize($header) }}</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                    @endif
                                                    <tbody>
                                                        @foreach($table['rows'] ?? [] as $row)
                                                            <tr>
                                                                @php
                                                                    $headerCount = count($table['headers'] ?? []);
                                                                    $rowCount = count($row);
                                                                @endphp

                                                                @if($rowCount === 2 && $headerCount > 2)
                                                                    <td>{{ $sanitize($row[0]) }}</td>
                                                                    <td colspan="{{ $headerCount - 1 }}">{{ $sanitize($row[1]) }}</td>
                                                                @else
                                                                    @foreach($row as $cell)
                                                                        <td>{{ $sanitize($cell) }}</td>
                                                                    @endforeach
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                @if(!empty($skill['terms']))
                                    <div class="subblock">
                                        <h3>Skill Terms</h3>
                                        <div class="terms">
                                            @foreach($skill['terms'] as $term)
                                                <div class="term">
                                                    <strong>{{ $sanitize($term['label'] ?? '-') }}</strong>
                                                    <div>{{ $sanitize($term['description'] ?? '-') }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($skill['notes']))
                                    <div class="subblock">
                                        <h3>Notes</h3>
                                        <ul class="list-clean">
                                            @foreach($skill['notes'] as $note)
                                                <li>{{ $sanitize($note) }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </article>
            @endif

            @if(!empty($storyIntro) || !empty($storyBio) || !empty($storySide))
                <article class="panel section">
                    <div class="section-head">
                        <div>
                            <h2>Lore Breakdown</h2>
                            <p>Bagian cerita utama, bio, dan side story yang diambil dari section Story.</p>
                        </div>
                    </div>

                    @if(!empty($story['quote']))
                        <blockquote class="quote">{{ $sanitize($story['quote']) }}</blockquote>
                    @endif

                    @if(!empty($storyIntro))
                        <div class="story-copy" style="margin-top:18px;">
                            @foreach($storyIntro as $paragraph)
                                <p>{{ $sanitize($paragraph) }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div class="story-columns">
                        @if(!empty($storyBio))
                            <div class="story-panel">
                                <h3>Bio</h3>
                                <div class="story-copy">
                                    @foreach($storyBio as $paragraph)
                                        <p>{{ $sanitize($paragraph) }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($storySide))
                            <div class="story-panel">
                                <h3>Side Story</h3>
                                <div class="story-copy">
                                    @foreach($storySide as $paragraph)
                                        <p>{{ $sanitize($paragraph) }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @endif

            @if(!empty($gallery))
                <article class="panel section">
                    <div class="section-head">
                        <div>
                            <h2>Media Gallery</h2>
                            <p>Seluruh gallery dibagi per kategori sesuai hasil scraping dari halaman fandom.</p>
                        </div>
                    </div>

                    <div class="gallery-groups">
                        @foreach($galleryBuckets as $bucketName => $groups)
                            <div class="gallery-bucket">
                                <h3>{{ $bucketName }}</h3>

                                <div class="gallery-subgroups">
                                    @foreach($groups as $groupName => $items)
                                        @php
                                            $normalizedGroup = strtolower($groupName);
                                            $galleryMode = 'skin';
                                            $groupClass = 'wide';

                                            if ($bucketName === 'Skins') {
                                                $galleryMode = 'skin';
                                            } elseif ($bucketName === 'Avatar') {
                                                $galleryMode = 'avatar';
                                            } elseif ($bucketName === 'Emotes') {
                                                $galleryMode = 'emote';
                                            }

                                            if (Str::contains($normalizedGroup, ['avatar', 'icon'])) {
                                                $groupClass = '';
                                            }

                                            if (Str::contains($normalizedGroup, ['painted', 'splash'])) {
                                                $groupClass = 'portrait';
                                            }
                                        @endphp

                                        <div class="gallery-subgroup">
                                            <h4 class="gallery-subgroup-title">{{ $sanitize($groupName) }}</h4>
                                            <div class="gallery-list is-{{ $galleryMode }}">
                                                @foreach($items as $item)
                                                    <figure class="gallery-card {{ $groupClass }}">
                                                        <img
                                                            src="{{ url('/hero-image?url=' . urlencode($item['image'])) }}"
                                                            alt="{{ $item['name'] }}"
                                                            loading="lazy"
                                                            decoding="async"
                                                        >
                                                        <figcaption>{{ $sanitize($item['name']) }}</figcaption>
                                                    </figure>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endif
        </main>

    </section>

    @if($links->isNotEmpty())
        <section class="links-footer">
            <div class="panel side-card">
                <h2>Official Links</h2>
                <div class="links-list">
                    @foreach($links as $link)
                        <a class="link-card" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">
                            <span>{{ $sanitize($link['label']) }}</span>
                            <small>{{ $link['url'] }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>

<script>
    document.querySelectorAll('.skill-variant-switcher').forEach((switcher) => {
        const buttons = Array.from(switcher.querySelectorAll('.skill-variant-button'));
        const panelIds = buttons.map((button) => button.dataset.variantTarget);
        const panels = panelIds
            .map((id) => document.getElementById(id))
            .filter(Boolean);

        const activateVariant = (targetId) => {
            buttons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.variantTarget === targetId);
            });

            panels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.id === targetId);
            });
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => activateVariant(button.dataset.variantTarget));
        });

        const initialButton = buttons.find((button) => button.classList.contains('is-active')) ?? buttons[0];
        if (initialButton) {
            activateVariant(initialButton.dataset.variantTarget);
        }
    });
</script>

</body>
</html>
