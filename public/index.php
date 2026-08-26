<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MC Barber - Barbería Premium. Experimenta el arte del corte perfecto con nuestros maestros barberos. Reserva tu cita hoy.">
    <meta name="keywords" content="barbería premium, corte de cabello, afeitado, barba, barbero profesional">
    <meta name="author" content="MC Barber">
    <title>MC Barber | Barbería Premium</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Inter:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@0,300;0,600;1,300&display=swap" rel="stylesheet">

    <style>
        /* === CSS CUSTOM PROPERTIES - DESIGN TOKENS === */
        :root {
            --gold:        #D4AF37;
            --gold-light:  #F3E5AB;
            --gold-dark:   #996515;
            --black:       #050505;
            --dark:        #0C0C0C;
            --dark-2:      #121212;
            --dark-3:      #1A1A1A;
            --dark-4:      #222222;
            --white:       #FAFAFA;
            --white-muted: #A0AAB2;
            --font-serif:  'Playfair Display', serif;
            --font-sans:   'Inter', sans-serif;
            --font-alt:    'Cormorant Garamond', serif;
            --transition:  cubic-bezier(0.19, 1, 0.22, 1);
            --shadow-gold: 0 10px 40px rgba(212, 175, 55, 0.15);
            --radius:      2px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; font-size: 16px; }
        body {
            background-color: var(--black);
            color: var(--white);
            font-family: var(--font-sans);
            font-weight: 300;
            line-height: 1.7;
            overflow-x: hidden;
        }
        img { display: block; max-width: 100%; }
        a   { color: inherit; text-decoration: none; }
        ul  { list-style: none; }

        ::-webkit-scrollbar             { width: 6px; }
        ::-webkit-scrollbar-track       { background: var(--dark); }
        ::-webkit-scrollbar-thumb       { background: var(--gold-dark); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--gold); }
        ::selection { background: var(--gold); color: var(--black); }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 9999;
            opacity: 0.4;
        }

        /* === UTILITIES === */
        .container { width: 90%; max-width: 1280px; margin: 0 auto; }

        .gold-line {
            display: inline-block; width: 60px; height: 2px;
            background: linear-gradient(90deg, var(--gold-dark), var(--gold-light));
            vertical-align: middle; margin: 0 12px;
        }

        .section-label {
            font-family: var(--font-sans); font-size: 0.7rem;
            letter-spacing: 0.35em; text-transform: uppercase;
            color: var(--gold); display: flex; align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-family: var(--font-serif);
            font-size: clamp(2.2rem, 5vw, 4rem);
            font-weight: 700; line-height: 1.15; margin-bottom: 1.5rem;
        }
        .section-title em { font-style: italic; color: var(--gold); }
        .text-muted { color: var(--white-muted); font-size: 0.95rem; }

        /* === BUTTONS === */
        .btn {
            display: inline-flex; align-items: center; gap: 12px;
            padding: 16px 40px; font-family: var(--font-sans);
            font-size: 0.85rem; font-weight: 600; letter-spacing: 0.25em;
            text-transform: uppercase; border: none; cursor: pointer;
            position: relative; overflow: hidden;
            transition: all 0.5s var(--transition);
            border-radius: var(--radius);
        }
        .btn::before {
            content: ''; position: absolute; inset: 0;
            background: rgba(255,255,255,0.1);
            transform: translateX(-100%);
            transition: transform 0.6s var(--transition);
        }
        .btn:hover::before { transform: translateX(0); }
        .btn-gold { 
            background: linear-gradient(135deg, var(--gold-dark), var(--gold), var(--gold-light)); 
            color: var(--black); 
            box-shadow: 0 10px 30px -10px rgba(212, 175, 55, 0.5);
        }
        .btn-gold:hover { 
            box-shadow: 0 15px 40px -10px rgba(212, 175, 55, 0.7); 
            transform: translateY(-2px); 
        }
        .btn-outline { 
            background: transparent; color: var(--gold); 
            border: 1px solid var(--gold); 
        }
        .btn-outline::before { background: var(--gold); }
        .btn-outline:hover { color: var(--black); }
        .btn span { position: relative; z-index: 1; }
        .btn svg  { position: relative; z-index: 1; transition: transform 0.3s; }
        .btn:hover svg { transform: translateX(4px); }


        /* === NAVIGATION === */
        #navbar {
            position: fixed; top: 0; left: 0; right: 0;
            z-index: 1000; padding: 30px 0;
            transition: all 0.6s var(--transition);
        }
        #navbar.scrolled {
            padding: 16px 0;
            background: rgba(10, 10, 10, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(212, 175, 55, 0.15);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8);
        }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; }
        .nav-logo  { display: flex; align-items: center; gap: 12px; }
        .nav-logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--gold-dark), var(--gold-light));
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
        }
        .nav-logo-text {
            font-family: var(--font-serif); font-size: 1.5rem; font-weight: 700;
            letter-spacing: 0.05em;
            background: linear-gradient(135deg, var(--white), var(--gold-light));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .nav-logo-sub {
            font-size: 0.55rem; letter-spacing: 0.25em; text-transform: uppercase;
            color: var(--gold); margin-top: -4px; font-family: var(--font-sans);
            -webkit-text-fill-color: var(--gold); display: block;
        }
        .nav-menu { display: flex; align-items: center; gap: 40px; }
        .nav-link {
            font-size: 0.75rem; letter-spacing: 0.18em; text-transform: uppercase;
            color: var(--white-muted); position: relative; transition: color 0.3s;
        }
        .nav-link::after {
            content: ''; position: absolute; bottom: -4px; left: 0;
            width: 0; height: 1px; background: var(--gold);
            transition: width 0.3s var(--transition);
        }
        .nav-link:hover { color: var(--white); }
        .nav-link:hover::after { width: 100%; }
        .nav-cta {
            font-size: 0.7rem; letter-spacing: 0.25em; text-transform: uppercase;
            padding: 12px 28px; background: transparent; font-weight: 600;
            border: 1px solid rgba(212, 175, 55, 0.5); color: var(--gold); 
            transition: all 0.4s var(--transition);
            position: relative; overflow: hidden; z-index: 1;
        }
        .nav-cta::before {
            content: ''; position: absolute; inset: 0; background: var(--gold);
            transform: scaleY(0); transform-origin: bottom; transition: transform 0.4s var(--transition); z-index: -1;
        }
        .nav-cta:hover { color: var(--black); border-color: var(--gold); }
        .nav-cta:hover::before { transform: scaleY(1); }
        .nav-hamburger {
            display: none; flex-direction: column; gap: 5px;
            cursor: pointer; padding: 4px; background: none; border: none;
        }
        .nav-hamburger span { display: block; width: 24px; height: 1.5px; background: var(--white); transition: all 0.3s; }

        #hero {
            position: relative; height: 100vh; min-height: 700px;
            display: flex; align-items: center; overflow: hidden;
        }
        .hero-bg {
            position: absolute; inset: 0;
            background-image: url('img/hero_barbershop_premium.jpg');
            background-size: cover; background-position: center;
            transform: scale(1.05);
            animation: heroPan 25s ease-in-out infinite alternate;
        }
        @keyframes heroPan {
            0%   { transform: scale(1.05) translateX(0); }
            100% { transform: scale(1.1) translateX(-2%); }
        }
        .hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(90deg, rgba(5,5,5,0.95) 0%, rgba(5,5,5,0.7) 40%, rgba(5,5,5,0.3) 100%);
        }
        .hero-overlay-bottom {
            position: absolute; bottom: 0; left: 0; right: 0; height: 250px;
            background: linear-gradient(to top, var(--black), transparent);
        }
        .hero-content { position: relative; z-index: 2; max-width: 750px; padding-left: 5%; }
        .hero-eyebrow {
            display: flex; align-items: center; gap: 16px; margin-bottom: 2rem;
            opacity: 0; animation: fadeInUp 0.8s var(--transition) 0.3s forwards;
        }
        .hero-eyebrow-line { width: 50px; height: 1px; background: var(--gold); }
        .hero-eyebrow-text { font-size: 0.75rem; letter-spacing: 0.4em; text-transform: uppercase; color: var(--gold); font-weight: 600; }
        .hero-title {
            margin-bottom: 2rem;
            opacity: 0; animation: fadeInUp 0.8s var(--transition) 0.5s forwards;
            letter-spacing: -0.02em; display: flex; flex-direction: column;
        }
        .hero-title .line-1 { 
            display: block; 
            font-family: var(--font-sans); 
            font-weight: 400; 
            font-size: clamp(1.5rem, 4vw, 2.8rem); 
            letter-spacing: 0.2em; 
            text-transform: uppercase; 
            color: var(--white);
            margin-bottom: -0.5rem;
            line-height: 1.2;
        }
        .hero-title .line-2 {
            display: block; font-family: var(--font-serif);
            font-size: clamp(4rem, 10vw, 9.5rem);
            font-weight: 900; font-style: italic;
            background: linear-gradient(135deg, var(--gold-light), var(--gold), var(--gold-dark));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            line-height: 1.1;
            padding-right: 15px; margin-left: -5px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.5));
        }
        .hero-desc {
            font-size: 1.05rem; color: var(--white-muted); max-width: 500px;
            margin-bottom: 3.5rem; line-height: 1.8;
            opacity: 0; animation: fadeInUp 0.8s var(--transition) 0.7s forwards;
        }
        .hero-buttons {
            display: flex; gap: 16px; flex-wrap: wrap;
            opacity: 0; animation: fadeInUp 0.8s var(--transition) 0.9s forwards;
        }
        .hero-stats {
            position: absolute; top: 50%; right: 5%; transform: translateY(-50%); z-index: 2;
            display: flex; flex-direction: column; gap: 40px; align-items: center;
            opacity: 0; animation: fadeInRight 1s var(--transition) 1.1s forwards;
        }
        .hero-stat { text-align: center; position: relative; padding-bottom: 40px; }
        .hero-stat:not(:last-child)::after {
            content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
            width: 40px; height: 1px; background: linear-gradient(to right, transparent, rgba(212,175,55,0.5), transparent);
        }
        .hero-stat-number {
            font-family: var(--font-serif); font-size: 3.5rem; font-weight: 900;
            color: var(--gold); line-height: 1; margin-bottom: 12px;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.8);
        }
        .hero-stat-label { font-size: 0.7rem; letter-spacing: 0.3em; text-transform: uppercase; color: rgba(255,255,255,0.9); font-weight: 600; text-shadow: 0 2px 10px rgba(0,0,0,0.8); }

        .scroll-indicator {
            position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%);
            z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 12px;
            opacity: 0; animation: fadeIn 1s var(--transition) 1.5s forwards;
        }
        .scroll-indicator span { font-size: 0.65rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--gold); font-weight: 600; }
        .scroll-mouse { width: 26px; height: 42px; border: 2px solid rgba(212, 175, 55, 0.4); border-radius: 13px; display: flex; justify-content: center; padding-top: 6px; }
        .scroll-wheel { width: 4px; height: 8px; background: var(--gold); border-radius: 2px; animation: scrollWheel 2s ease infinite; }
        @keyframes scrollWheel { 0% { opacity: 1; transform: translateY(0); } 100% { opacity: 0; transform: translateY(12px); } }

        #about { padding: 120px 0; position: relative; overflow: hidden; }
        #about::before {
            content: 'MC'; position: absolute; right: -2%; top: 50%; transform: translateY(-50%);
            font-family: var(--font-serif); font-size: 30vw; font-weight: 900;
            color: rgba(201,168,76,0.025); pointer-events: none; line-height: 1;
        }
        .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }
        .about-image-wrap { position: relative; padding-right: 40px; padding-bottom: 40px; }
        .about-image-main { width: 100%; aspect-ratio: 4/5; object-fit: cover; position: relative; z-index: 2; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .about-image-frame { 
            position: absolute; top: 40px; left: 40px; right: 0; bottom: 0; 
            border: 2px solid rgba(212,175,55,0.3); z-index: 1; border-radius: 24px;
            background: repeating-linear-gradient(45deg, rgba(212,175,55,0.03) 0, rgba(212,175,55,0.03) 2px, transparent 2px, transparent 10px);
        }
        .about-image-badge {
            position: absolute; bottom: 10px; right: 10px;
            width: 140px; height: 140px;
            background: rgba(20,20,20,0.7); backdrop-filter: blur(12px);
            border-radius: 50%; display: flex; flex-direction: column;
            align-items: center; justify-content: center; z-index: 3;
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
            border: 1px solid rgba(212,175,55,0.4);
            animation: floatBadge 6s ease-in-out infinite;
        }
        @keyframes floatBadge {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .about-badge-number { font-family: var(--font-serif); font-size: 2.8rem; font-weight: 900; color: var(--gold); line-height: 1; text-shadow: 0 2px 10px rgba(212,175,55,0.3); margin-bottom: 4px; }
        .about-badge-text { font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--white); text-align: center; line-height: 1.4; font-weight: 600; }
        .about-features { margin-top: 3.5rem; display: flex; flex-direction: column; gap: 24px; }
        .about-feature { 
            display: flex; gap: 24px; align-items: flex-start; padding: 24px; 
            background: linear-gradient(90deg, rgba(255,255,255,0.02), transparent);
            border-left: 2px solid rgba(212,175,55,0.2); 
            border-radius: 0 16px 16px 0;
            transition: all 0.4s cubic-bezier(0.19, 1, 0.22, 1); cursor: default; 
        }
        .about-feature:hover { border-left-color: var(--gold); background: linear-gradient(90deg, rgba(212,175,55,0.06), transparent); transform: translateX(10px); }
        .about-feature-icon { 
            width: 54px; height: 54px; border-radius: 50%;
            background: rgba(212,175,55,0.08); color: var(--gold);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; 
            border: 1px solid rgba(212,175,55,0.2);
            transition: all 0.4s;
        }
        .about-feature:hover .about-feature-icon { background: var(--gold); border-color: var(--gold); transform: scale(1.1); box-shadow: 0 0 20px rgba(212,175,55,0.4); color: var(--black); }
        .about-feature-icon svg { transition: all 0.4s; }
        .about-feature-title { font-family: var(--font-serif); font-size: 1.25rem; font-weight: 700; margin-bottom: 6px; color: var(--white); transition: color 0.4s; }
        .about-feature:hover .about-feature-title { color: var(--gold-light); }
        .about-feature-desc { font-size: 0.9rem; color: var(--white-muted); line-height: 1.6; }

        #services { padding: 120px 0; background: var(--dark); position: relative; }
        #services::after { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px; background: linear-gradient(90deg, transparent, var(--gold), transparent); }
        .services-header { text-align: center; margin-bottom: 70px; }
        .services-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .service-card { 
            position: relative; overflow: hidden; cursor: pointer; 
            border-radius: 20px; aspect-ratio: 3/4;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            border: 1px solid rgba(212,175,55,0.1);
        }
        .service-card-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s cubic-bezier(0.19, 1, 0.22, 1); display: block; filter: brightness(0.85); }
        .service-card:hover .service-card-img { transform: scale(1.1); filter: brightness(1); }
        .service-card-overlay { 
            position: absolute; inset: 0; 
            background: linear-gradient(to top, rgba(5,5,5,0.95) 0%, rgba(5,5,5,0.6) 40%, transparent 100%); 
            transition: all 0.5s; 
        }
        .service-card:hover .service-card-overlay {
            background: linear-gradient(to top, rgba(5,5,5,0.95) 0%, rgba(5,5,5,0.8) 50%, rgba(5,5,5,0.4) 100%);
        }
        .service-card-content { 
            position: absolute; bottom: 0; left: 0; right: 0; 
            padding: 40px 32px; 
            display: flex; flex-direction: column; gap: 4px;
            transform: translateY(20px); transition: transform 0.5s cubic-bezier(0.19, 1, 0.22, 1);
        }
        .service-card:hover .service-card-content { transform: translateY(0); }
        .service-card-number { 
            font-family: var(--font-serif); font-size: 4rem; font-weight: 900; 
            color: transparent; -webkit-text-stroke: 1px rgba(212,175,55,0.3);
            line-height: 1; margin-bottom: -16px; 
            transition: all 0.5s;
        }
        .service-card:hover .service-card-number { color: rgba(212,175,55,0.15); -webkit-text-stroke: 0px; }
        .service-card-name { font-family: var(--font-serif); font-size: 1.8rem; font-weight: 700; color: var(--white); }
        .service-card-price { color: var(--gold); font-size: 1rem; letter-spacing: 0.1em; font-weight: 600; margin-bottom: 8px; }
        .service-card-desc { 
            font-size: 0.9rem; color: var(--white-muted); 
            line-height: 1.6; 
            max-height: 0; overflow: hidden; opacity: 0; 
            transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1); 
        }
        .service-card:hover .service-card-desc { max-height: 120px; opacity: 1; padding-top: 10px; }
        .service-card-tag { 
            display: inline-flex; align-items: center; gap: 8px; 
            font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; 
            color: var(--black); background: var(--gold); 
            padding: 6px 16px; border-radius: 30px; 
            margin-top: 16px; width: fit-content;
            transform: translateY(15px); opacity: 0; transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1) 0.1s; 
            font-weight: 600;
        }
        .service-card:hover .service-card-tag { transform: translateY(0); opacity: 1; }
        .service-card-tag svg { color: var(--black); }
        
        .services-pricing { margin-top: 80px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 40px 80px; }
        .pricing-item { 
            display: flex; align-items: center; justify-content: space-between; 
            padding-bottom: 16px; border-bottom: 1px dashed rgba(212,175,55,0.3); 
            transition: all 0.4s var(--transition); cursor: default; position: relative;
        }
        .pricing-item:hover { border-bottom-style: solid; border-bottom-color: var(--gold); transform: translateX(10px); }
        .pricing-item-name { font-family: var(--font-serif); font-size: 1.3rem; font-weight: 600; color: var(--white); }
        .pricing-item-duration { font-size: 0.7rem; color: var(--gold); letter-spacing: 0.15em; text-transform: uppercase; margin-top: 4px; }
        .pricing-item-price { font-family: var(--font-serif); font-size: 1.6rem; font-weight: 700; color: var(--gold-light); }

        #team { padding: 120px 0; background: var(--black); }
        .team-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 70px; }
        .team-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; margin-top: 40px; }
        .team-card { position: relative; transition: all 0.5s var(--transition); display: flex; flex-direction: column; align-items: center; }
        .team-card:hover { transform: translateY(-10px); }
        .team-card-img-wrap { 
            position: relative; overflow: hidden; width: 100%; aspect-ratio: 3/4;
            border-radius: 240px 240px 16px 16px; 
            border: 1px solid rgba(212,175,55,0.15); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            margin-bottom: -50px;
            z-index: 1;
        }
        .team-card-img { 
            width: 100%; height: 100%; object-fit: cover; object-position: center 10%; 
            filter: grayscale(10%) contrast(110%); 
            transition: transform 0.7s var(--transition), filter 0.7s; 
        }
        .team-card:hover .team-card-img { transform: scale(1.08); filter: grayscale(0%) contrast(100%); }
        .team-card-social { 
            position: absolute; right: 20px; top: 120px; 
            transform: translateX(80px); display: flex; flex-direction: column; gap: 12px; 
            transition: transform 0.5s cubic-bezier(0.19, 1, 0.22, 1) 0.1s; 
        }
        .team-card:hover .team-card-social { transform: translateX(0); }
        .social-btn { 
            width: 44px; height: 44px; border-radius: 50%;
            background: rgba(10,10,10,0.6); backdrop-filter: blur(8px);
            border: 1px solid rgba(212,175,55,0.4); 
            display: flex; align-items: center; justify-content: center; 
            color: var(--gold);
            transition: all 0.3s; cursor: pointer; 
        }
        .social-btn:hover { background: var(--gold); border-color: var(--gold); color: var(--black); transform: scale(1.1); }
        .social-btn svg { width: 18px; height: 18px; }
        .team-card-info { 
            background: linear-gradient(145deg, rgba(20,20,20,0.9), rgba(10,10,10,0.95)); 
            border: 1px solid rgba(212,175,55,0.2); 
            padding: 32px 24px; text-align: center;
            border-radius: 16px; position: relative; z-index: 2;
            width: 90%; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.6), inset 0 0 20px rgba(212,175,55,0.03);
            backdrop-filter: blur(16px);
            transition: all 0.5s var(--transition);
        }
        .team-card:hover .team-card-info { 
            border-color: rgba(212,175,55,0.5); 
            box-shadow: 0 20px 50px rgba(0,0,0,0.7), inset 0 0 30px rgba(212,175,55,0.05); 
        }
        .team-card-role { font-size: 0.65rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--gold); margin-bottom: 8px; font-weight: 600; }
        .team-card-name { font-family: var(--font-serif); font-size: 1.6rem; font-weight: 700; margin-bottom: 12px; color: var(--white); }
        .team-card-desc { font-size: 0.85rem; color: var(--white-muted); margin-bottom: 20px; line-height: 1.6; }
        .team-card-specialties { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
        .specialty-tag { 
            font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase; 
            color: var(--gold-light); background: rgba(212,175,55,0.08);
            border: 1px solid rgba(212,175,55,0.2); border-radius: 40px; padding: 6px 14px; 
            transition: all 0.3s;
        }
        .specialty-tag:hover { background: rgba(212,175,55,0.2); border-color: var(--gold); color: var(--white); }

        #testimonials { padding: 120px 0; background: var(--dark); position: relative; overflow: hidden; }
        #testimonials::before { content: '"'; position: absolute; top: -20px; left: -20px; font-family: var(--font-serif); font-size: 40vw; color: rgba(201,168,76,0.03); line-height: 1; pointer-events: none; }
        .testimonials-header { text-align: center; margin-bottom: 70px; }
        .testimonials-slider { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .testimonial-card { 
            background: linear-gradient(145deg, rgba(20,20,20,0.8), rgba(10,10,10,0.9)); 
            border: 1px solid rgba(212,175,55,0.1); 
            padding: 40px; 
            border-radius: 16px;
            position: relative; 
            transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .testimonial-card::after {
            content: ''; position: absolute; inset: 0; border-radius: 16px;
            box-shadow: inset 0 0 20px rgba(212, 175, 55, 0.02); pointer-events: none;
        }
        .testimonial-card:hover { 
            border-color: rgba(212,175,55,0.4); 
            transform: translateY(-8px); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.6), 0 10px 20px rgba(212,175,55,0.1); 
            background: linear-gradient(145deg, rgba(25,25,25,0.9), rgba(15,15,15,1));
        }
        .testimonial-card::before { 
            content: '"'; position: absolute; top: 10px; right: 24px; 
            font-family: var(--font-serif); font-size: 6rem; 
            background: linear-gradient(135deg, rgba(212,175,55,0.4), rgba(212,175,55,0.05));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            line-height: 1; pointer-events: none;
        }
        .testimonial-stars { display: flex; gap: 6px; margin-bottom: 24px; }
        .star { color: var(--gold); font-size: 1rem; filter: drop-shadow(0 0 4px rgba(212,175,55,0.4)); }
        .testimonial-text { 
            font-family: var(--font-alt); font-size: 1.15rem; font-style: italic; 
            color: var(--white); line-height: 1.7; margin-bottom: 32px; flex-grow: 1;
            position: relative; z-index: 1; font-weight: 300;
        }
        .testimonial-author { display: flex; align-items: center; gap: 16px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px; }
        .testimonial-avatar { 
            width: 56px; height: 56px; border-radius: 50%; 
            object-fit: cover; background: var(--dark-3);
            display: block; flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5); border: 2px solid rgba(212,175,55,0.4);
            transition: all 0.4s var(--transition);
        }
        .testimonial-card:hover .testimonial-avatar {
            border-color: var(--gold);
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(212,175,55,0.3);
        }
        .testimonial-author-name { font-weight: 600; font-size: 0.95rem; color: var(--white); }
        .testimonial-author-role { font-size: 0.75rem; color: var(--gold); letter-spacing: 0.05em; text-transform: uppercase; margin-top: 2px; }

        #booking { padding: 120px 0; position: relative; overflow: hidden; background: var(--dark-2); }
        .booking-bg { 
            position: absolute; inset: 0; 
            background: radial-gradient(circle at 50% 50%, rgba(212,175,55,0.05) 0%, transparent 60%),
                        linear-gradient(135deg, rgba(10,10,10,0.95) 0%, rgba(5,5,5,1) 100%);
        }
        .booking-overlay { display: none; }
        .booking-content { position: relative; z-index: 2; max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; align-items: center; }
        .booking-header { display: flex; flex-direction: column; align-items: center; }
        .booking-info { 
            display: grid; grid-template-columns: repeat(3, 1fr); 
            margin-top: 50px; width: 100%; text-align: center; 
            background: rgba(15,15,15,0.5);
            border: 1px solid rgba(212,175,55,0.15);
            border-radius: 24px; 
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            overflow: hidden; position: relative;
        }
        .booking-info::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212,175,55,0.3), transparent);
        }
        .booking-info-item { 
            display: flex; flex-direction: column; align-items: center; gap: 14px; 
            padding: 56px 32px; position: relative; transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1); 
        }
        .booking-info-item:not(:last-child)::after {
            content: ''; position: absolute; right: 0; top: 20%; bottom: 20%; width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.08), transparent);
        }
        .booking-info-item:hover { background: rgba(201,168,76,0.04); }
        .booking-info-icon { 
            width: 80px; height: 80px; border-radius: 50%; 
            background: rgba(10, 10, 10, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; 
            transition: all 0.6s cubic-bezier(0.19, 1, 0.22, 1); 
            color: var(--gold);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4), inset 0 0 0 1px rgba(212, 175, 55, 0.2);
            margin-bottom: 16px;
            position: relative;
        }
        .booking-info-icon::after {
            content: ''; position: absolute; inset: 0; border-radius: 50%;
            box-shadow: inset 0 0 0 1px var(--gold);
            opacity: 0; transform: scale(1.2); transition: all 0.6s cubic-bezier(0.19, 1, 0.22, 1);
        }
        .booking-info-icon svg {
            filter: drop-shadow(0 0 8px rgba(212, 175, 55, 0.2));
            transition: transform 0.6s cubic-bezier(0.19, 1, 0.22, 1);
        }
        .booking-info-item:hover .booking-info-icon { 
            transform: translateY(-8px); 
            background: rgba(15, 15, 15, 0.8);
            color: var(--gold-light);
            box-shadow: 0 20px 40px rgba(0,0,0,0.6), inset 0 0 0 1px rgba(212, 175, 55, 0.5); 
        }
        .booking-info-item:hover .booking-info-icon::after {
            opacity: 0.3; transform: scale(1.15);
        }
        .booking-info-item:hover .booking-info-icon svg {
            transform: scale(1.1);
            filter: drop-shadow(0 0 12px rgba(212, 175, 55, 0.5));
        }
        .booking-info-label { font-size: 0.7rem; letter-spacing: 0.3em; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 4px; font-weight: 600; }
        .booking-info-value { font-size: 1.25rem; font-family: var(--font-serif); font-weight: 700; color: var(--gold-light); }
        .booking-info-sub { font-size: 0.8rem; color: var(--white-muted); margin-top: 4px; }
        .booking-cta-wrap { display: flex; justify-content: center; }

        #gallery-section { padding: 120px 0; background: var(--black); position: relative; }
        .gallery-header { text-align: center; margin-bottom: 60px; }
        #gallery { 
            display: grid; grid-template-columns: repeat(4, 1fr); 
            grid-template-rows: repeat(2, 280px); gap: 20px; 
        }
        .gallery-item { position: relative; overflow: hidden; cursor: pointer; border-radius: 16px; }
        .gallery-item:first-child { grid-column: 1 / 3; grid-row: 1 / 3; }
        .gallery-item:nth-child(2) { grid-column: 3 / 5; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 1s cubic-bezier(0.19, 1, 0.22, 1); filter: brightness(0.7); }
        .gallery-item:hover img { transform: scale(1.08); filter: brightness(1.1); }
        .gallery-item::after {
            content: ''; position: absolute; inset: 0;
            border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; pointer-events: none;
            transition: border-color 0.4s;
        }
        .gallery-item:hover::after { border-color: rgba(212,175,55,0.5); }

        #footer { 
            background: var(--dark); 
            padding: 100px 0 0; 
            position: relative;
        }
        #footer::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212,175,55,0.4), transparent);
            box-shadow: 0 0 20px rgba(212,175,55,0.2);
        }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1.2fr; gap: 60px; padding-bottom: 80px; }
        .footer-brand-logo { font-family: var(--font-serif); font-size: 2.5rem; font-weight: 900; background: linear-gradient(135deg, var(--white), var(--gold-light)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 8px; letter-spacing: -0.02em; }
        .footer-brand-tagline { font-size: 0.65rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--gold); margin-bottom: 24px; font-weight: 600; }
        .footer-brand-desc { font-size: 0.9rem; color: var(--white-muted); line-height: 1.9; max-width: 320px; margin-bottom: 32px; }
        .footer-social { display: flex; gap: 16px; }
        .footer-social-btn { 
            width: 44px; height: 44px; border-radius: 50%;
            border: 1px solid rgba(212,175,55,0.2); 
            display: flex; align-items: center; justify-content: center; 
            color: var(--white-muted); transition: all 0.4s var(--transition); cursor: pointer;
            background: rgba(10,10,10,0.5);
        }
        .footer-social-btn:hover { 
            border-color: var(--gold); 
            background: var(--gold); 
            color: var(--black);
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(212,175,55,0.2);
        }
        .footer-col-title { font-family: var(--font-sans); font-size: 0.85rem; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 32px; color: var(--white); }
        .footer-links { display: flex; flex-direction: column; gap: 16px; }
        .footer-link { font-size: 0.9rem; color: var(--white-muted); display: inline-flex; align-items: center; transition: all 0.3s; position: relative; width: fit-content; }
        .footer-link::after { 
            content: ''; position: absolute; bottom: -4px; left: 0; width: 0; height: 1px; 
            background: var(--gold); transition: width 0.3s var(--transition); 
        }
        .footer-link:hover { color: var(--gold); transform: translateX(4px); }
        .footer-link:hover::after { width: 100%; }
        .footer-contact-item { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 24px; }
        .footer-contact-icon-wrap {
            width: 36px; height: 36px; border-radius: 50%; background: rgba(212,175,55,0.08);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            border: 1px solid rgba(212,175,55,0.15); transition: all 0.3s;
        }
        .footer-contact-item:hover .footer-contact-icon-wrap { background: var(--gold); border-color: var(--gold); }
        .footer-contact-item:hover .footer-contact-icon { color: var(--black); }
        .footer-contact-icon { color: var(--gold); transition: all 0.3s; width: 16px; height: 16px; }
        .footer-contact-text { font-size: 0.9rem; color: var(--white-muted); line-height: 1.6; padding-top: 6px; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.05); padding: 32px 0; display: flex; align-items: center; justify-content: space-between; }
        .footer-copy { font-size: 0.8rem; color: rgba(255,255,255,0.4); }
        .footer-copy span { color: var(--gold); font-weight: 600; }
        .footer-bottom-links { display: flex; gap: 32px; }
        .footer-bottom-link { font-size: 0.8rem; color: rgba(255,255,255,0.4); transition: color 0.3s; }
        .footer-bottom-link:hover { color: var(--gold); }

        .reveal { opacity: 0; transform: translateY(30px); transition: opacity 0.7s var(--transition), transform 0.7s var(--transition); }
        .reveal.revealed { opacity: 1; transform: translateY(0); }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }

        @keyframes fadeInUp   { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn     { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInRight{ from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }

        .whatsapp-float {
            position: fixed; bottom: 32px; right: 32px; z-index: 900;
            width: 64px; height: 64px; background: linear-gradient(135deg, #25D366, #128C7E); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 40px rgba(37,211,102,0.4); transition: all 0.4s var(--transition); cursor: pointer;
        }
        .whatsapp-float:hover { transform: scale(1.1) translateY(-6px); box-shadow: 0 15px 50px rgba(37,211,102,0.6); }
        .whatsapp-float::before { content: ''; position: absolute; inset: -8px; border-radius: 50%; border: 2px solid rgba(37,211,102,0.4); animation: waPulse 2s var(--transition) infinite; }
        @keyframes waPulse { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(1.5); opacity: 0; } }
        @media (max-width: 1024px) {
            .about-grid          { grid-template-columns: 1fr; gap: 60px; }
            .about-image-wrap    { max-width: 480px; }
            .services-grid       { grid-template-columns: 1fr 1fr; }
            .services-pricing    { grid-template-columns: 1fr 1fr; }
            .team-grid           { grid-template-columns: 1fr 1fr; }
            .testimonials-slider { grid-template-columns: 1fr 1fr; }
            .booking-info        { grid-template-columns: 1fr 1fr; gap: 32px; }
            .footer-grid         { grid-template-columns: 1fr 1fr; }
            #gallery             { grid-template-columns: repeat(3,1fr); grid-template-rows: repeat(2, 220px); }
            #gallery .gallery-item:first-child { grid-column: 1; grid-row: 1 / 3; }
        }
        @media (max-width: 768px) {
            .nav-menu      { display: none; }
            .nav-hamburger { display: flex; }
            .nav-menu.open { display: flex; flex-direction: column; position: fixed; inset: 0; background: rgba(10,10,10,0.98); align-items: center; justify-content: center; gap: 32px; z-index: 999; }
            .nav-menu.open .nav-link { font-size: 1.2rem; }
            .hero-stats          { display: none; }
            .services-grid       { grid-template-columns: 1fr; }
            .services-pricing    { grid-template-columns: 1fr; }
            .team-grid           { grid-template-columns: 1fr; }
            .testimonials-slider { grid-template-columns: 1fr; }
            .booking-info        { grid-template-columns: 1fr; gap: 0; }
            .booking-info-item:not(:last-child)::after {
                width: 70%; height: 1px; top: auto; bottom: 0; right: 15%; left: 15%;
                background: linear-gradient(to right, transparent, rgba(255,255,255,0.08), transparent);
            }
            .footer-grid         { grid-template-columns: 1fr; }
            .team-header         { flex-direction: column; align-items: flex-start; gap: 24px; }
            #gallery             { grid-template-columns: 1fr 1fr; grid-auto-rows: 200px; grid-template-rows: none; }
            #gallery .gallery-item:first-child { grid-column: 1 / 3; grid-row: auto; }
            .hero-buttons        { flex-direction: column; }
            .hero-buttons .btn   { width: 100%; justify-content: center; }
        }
    </style>
</head>

<body>

<nav id="navbar">
    <div class="container">
        <div class="nav-inner">
            <a href="#hero" class="nav-logo">
                <div class="nav-logo-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#050505" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="6" cy="6" r="3"></circle>
                        <circle cx="6" cy="18" r="3"></circle>
                        <line x1="20" y1="4" x2="8.12" y2="15.88"></line>
                        <line x1="14.47" y1="14.48" x2="20" y2="20"></line>
                        <line x1="8.12" y1="8.12" x2="12" y2="12"></line>
                    </svg>
                </div>
                <div>
                    <div class="nav-logo-text">MC Barber</div>
                    <span class="nav-logo-sub">Barbería Premium</span>
                </div>
            </a>

            <ul class="nav-menu" id="navMenu">
                <li><a href="#about"        class="nav-link">Nosotros</a></li>
                <li><a href="#services"     class="nav-link">Servicios</a></li>
                <li><a href="#team"         class="nav-link">Equipo</a></li>
                <li><a href="#testimonials" class="nav-link">Reseñas</a></li>
                <li><a href="../views/auth/login.php" class="nav-cta">Reservar Cita</a></li>
            </ul>

            <button class="nav-hamburger" id="hamburger" aria-label="Abrir menú" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<section id="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-overlay-bottom"></div>

    <div class="container">
        <div class="hero-content">
            <div class="hero-eyebrow">
                <div class="hero-eyebrow-line"></div>
                <span class="hero-eyebrow-text">Barbería Premium &middot; Desde 2026</span>
            </div>

            <h1 class="hero-title">
                <span class="line-1">El Arte del</span>
                <span class="line-2">Estilo Perfecto</span>
            </h1>

            <p class="hero-desc">
                Donde la tradición del barbero clásico se fusiona con la precisión moderna.
                Cada corte es una obra maestra diseñada para ti.
            </p>

            <div class="hero-buttons">
                <a href="../views/auth/login.php" class="btn btn-gold" id="hero-cta-booking">
                    <span>Reservar Cita</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="#services" class="btn btn-outline" id="hero-cta-services">
                    <span>Ver Servicios</span>
                </a>
            </div>
        </div>
    </div>

    <div class="hero-stats">
        <div class="hero-stat">
            <div class="hero-stat-number" id="stat-years">10+</div>
            <div class="hero-stat-label">Años de<br>Experiencia</div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-number" id="stat-clients">3K+</div>
            <div class="hero-stat-label">Clientes<br>Satisfechos</div>
        </div>
        <div class="hero-stat">
            <div class="hero-stat-number"><span>5</span><span style="font-family: var(--font-sans); font-size: 2.2rem; transform: translateY(-4px);">★</span></div>
            <div class="hero-stat-label">Calificación<br>Promedio</div>
        </div>
    </div>
</section>

<section id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-image-wrap reveal">
                <img src="img/about_luxury.jpg" alt="Interior MC Barber" class="about-image-main" loading="lazy">
                <div class="about-image-frame"></div>
                <div class="about-image-badge">
                    <div class="about-badge-number">10</div>
                    <div class="about-badge-text">Años de<br>Excelencia</div>
                </div>
            </div>

            <div>
                <div class="section-label reveal"><span class="gold-line"></span>Nuestra Historia</div>
                <h2 class="section-title reveal reveal-delay-1">
                    Más que un<br>corte, una <em>experiencia</em>
                </h2>
                <p class="text-muted reveal reveal-delay-2">
                    En MC Barber, hemos perfeccionado el arte del barbero durante más de una década.
                    Cada cliente recibe un trato exclusivo, personalizado y elevado a la más alta calidad.
                    Combinamos técnicas tradicionales con las tendencias más modernas del grooming masculino.
                </p>

                <div class="about-features">
                    <div class="about-feature reveal reveal-delay-2">
                        <div class="about-feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/>
                            </svg>
                        </div>
                        <div>
                            <div class="about-feature-title">Productos Premium</div>
                            <div class="about-feature-desc">Usamos exclusivamente productos de alta gama, cuidando la salud de tu cabello y piel.</div>
                        </div>
                    </div>
                    <div class="about-feature reveal reveal-delay-3">
                        <div class="about-feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                        </div>
                        <div>
                            <div class="about-feature-title">Maestros Barberos</div>
                            <div class="about-feature-desc">Nuestro equipo cuenta con años de formación y certificaciones internacionales.</div>
                        </div>
                    </div>
                    <div class="about-feature reveal reveal-delay-4">
                        <div class="about-feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="about-feature-title">Ambiente Exclusivo</div>
                            <div class="about-feature-desc">Un espacio diseñado para relajarte y disfrutar de una experiencia verdaderamente premium.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="services">
    <div class="container">
        <div class="services-header">
            <div class="section-label reveal" style="justify-content:center;">
                <span class="gold-line"></span>Lo Que Ofrecemos<span class="gold-line"></span>
            </div>
            <h2 class="section-title reveal reveal-delay-1">Nuestros <em>Servicios</em></h2>
            <p class="text-muted reveal reveal-delay-2" style="max-width:520px;margin:0 auto;">
                Cada servicio está diseñado con atención al detalle y el más alto estándar de calidad.
            </p>
        </div>

        <div class="services-grid">
            <?php
            $services = [
                ['img'=>'img/service_1.jpg','num'=>'01','name'=>'Corte Clásico','price'=>'Desde $150','desc'=>'Cortes precisos adaptados a tu estilo y estructura facial con técnicas clásicas y modernas.','tag'=>'Más Popular'],
                ['img'=>'img/service_2.jpg',  'num'=>'02','name'=>'Afeitado & Barba','price'=>'Desde $120','desc'=>'Afeitado con navaja, toalla caliente y productos premium para la piel más suave.','tag'=>'Premium'],
                ['img'=>'img/service_3.jpg',        'num'=>'03','name'=>'Corte + Barba','price'=>'Desde $220','desc'=>'El paquete completo: corte y arreglo de barba para lucir impecable de la cabeza al cuello.','tag'=>'Recomendado'],
            ];
            foreach ($services as $s):
            ?>
            <div class="service-card">
                <img src="<?= htmlspecialchars($s['img']) ?>" alt="<?= htmlspecialchars($s['name']) ?>" class="service-card-img" loading="lazy">
                <div class="service-card-overlay"></div>
                <div class="service-card-content">
                    <div class="service-card-number"><?= $s['num'] ?></div>
                    <div class="service-card-name"><?= htmlspecialchars($s['name']) ?></div>
                    <div class="service-card-price"><?= htmlspecialchars($s['price']) ?></div>
                    <div class="service-card-desc"><?= htmlspecialchars($s['desc']) ?></div>
                    <div class="service-card-tag">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"/></svg>
                        <?= htmlspecialchars($s['tag']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="services-pricing">
            <?php
            $prices = [
                ['name'=>'Degradado (Fade)',          'dur'=>'45 min','price'=>'$180'],
                ['name'=>'Tintura de Cabello',        'dur'=>'60 min','price'=>'$300'],
                ['name'=>'Diseño de Líneas',          'dur'=>'30 min','price'=>'$100'],
                ['name'=>'Tratamiento Capilar',       'dur'=>'45 min','price'=>'$200'],
                ['name'=>'Arreglo de Cejas',          'dur'=>'20 min','price'=>'$80'],
                ['name'=>'Masaje de Cuero Cabelludo', 'dur'=>'20 min','price'=>'$120'],
            ];
            foreach ($prices as $p):
            ?>
            <div class="pricing-item reveal">
                <div>
                    <div class="pricing-item-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="pricing-item-duration"><?= htmlspecialchars($p['dur']) ?></div>
                </div>
                <div class="pricing-item-price"><?= htmlspecialchars($p['price']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="team">
    <div class="container">
        <div class="team-header">
            <div>
                <div class="section-label reveal"><span class="gold-line"></span>Nuestros Profesionales</div>
                <h2 class="section-title reveal reveal-delay-1">Maestros del <em>Arte</em></h2>
            </div>
            <a href="#booking" class="btn btn-outline reveal reveal-delay-2" id="team-booking-cta">
                <span>Reserva con tu barbero</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="team-grid">
            <?php
            $team = [
                ['img'=>'img/team_1.jpg','role'=>'Master Barber · Fundador','name'=>'Miguel Cortés','desc'=>'Con 15 años de experiencia, Miguel es el alma de MC Barber. Especialista en degradados y diseños personalizados.','specs'=>['Fade','Diseño','Clásico']],
                ['img'=>'img/team_2.jpg','role'=>'Senior Barber',            'name'=>'Carlos Mendoza','desc'=>'Experto en barbas y afeitado clásico con navaja. Su precisión y detalles son incomparables.','specs'=>['Barba','Afeitado','Skincare']],
                ['img'=>'img/team_3.jpg','role'=>'Hair Stylist',             'name'=>'Andrés Rivera','desc'=>'Especialista en tendencias internacionales y tintura. Siempre a la vanguardia del estilo masculino.','specs'=>['Tintura','Tendencias','Texturizado']],
            ];
            foreach ($team as $m):
            ?>
            <div class="team-card reveal">
                <div class="team-card-img-wrap">
                    <img src="<?= htmlspecialchars($m['img']) ?>" alt="<?= htmlspecialchars($m['name']) ?>" class="team-card-img" loading="lazy">
                    <div class="team-card-social">
                        <div class="social-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                        </div>
                        <div class="social-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="team-card-info">
                    <div class="team-card-role"><?= htmlspecialchars($m['role']) ?></div>
                    <div class="team-card-name"><?= htmlspecialchars($m['name']) ?></div>
                    <div class="team-card-desc"><?= htmlspecialchars($m['desc']) ?></div>
                    <div class="team-card-specialties">
                        <?php foreach ($m['specs'] as $s): ?><span class="specialty-tag"><?= htmlspecialchars($s) ?></span><?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="testimonials">
    <div class="container">
        <div class="testimonials-header">
            <div class="section-label reveal" style="justify-content:center;"><span class="gold-line"></span>Opiniones Reales<span class="gold-line"></span></div>
            <h2 class="section-title reveal reveal-delay-1">Lo Que Dicen<br>Nuestros <em>Clientes</em></h2>
        </div>
        <div class="testimonials-slider">
            <?php
            $reviews = [
                ['stars'=>5,'text'=>'"La mejor experiencia de barbería que he tenido en mi vida. Miguel tiene una precisión increíble y el ambiente es de primer nivel."','name'=>'Roberto A.','role'=>'Cliente desde 2019','img'=>'img/client1.jpg'],
                ['stars'=>5,'text'=>'"Vine por el degradado y salí con el mejor corte de mi vida. El ambiente es espectacular, parece una barbería de película."','name'=>'David M.','role'=>'Cliente habitual','img'=>'img/client2.jpg'],
                ['stars'=>5,'text'=>'"Carlos hizo un trabajo increíble con mi barba. El afeitado con navaja fue una experiencia de lujo total. ¡Totalmente recomendado!"','name'=>'Fernando L.','role'=>'Cliente desde 2021','img'=>'img/client3.jpg'],
            ];
            foreach ($reviews as $r):
            ?>
            <div class="testimonial-card reveal">
                <div class="testimonial-stars"><?php for($i=0;$i<$r['stars'];$i++) echo '<span class="star">★</span>'; ?></div>
                <div class="testimonial-text"><?= $r['text'] ?></div>
                <div class="testimonial-author">
                    <img src="<?= htmlspecialchars($r['img']) ?>" alt="<?= htmlspecialchars($r['name']) ?>" class="testimonial-avatar" loading="lazy">
                    <div>
                        <div class="testimonial-author-name"><?= htmlspecialchars($r['name']) ?></div>
                        <div class="testimonial-author-role"><?= htmlspecialchars($r['role']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="booking">
    <div class="booking-bg"></div>
    <div class="booking-overlay"></div>

    <div class="container">
        <div class="booking-content">
            <div class="booking-header">
                <div class="section-label reveal" style="justify-content:center;"><span class="gold-line"></span>Reserva tu Cita<span class="gold-line"></span></div>
                <h2 class="section-title reveal reveal-delay-1" style="text-align:center;">Tu Próxima<br><em>Transformación</em><br>Te Espera</h2>
                <p class="text-muted reveal reveal-delay-2" style="max-width: 600px; margin: 0 auto 2rem; text-align:center;">
                    Agenda tu cita en minutos. Elige tu barbero favorito, el servicio que deseas
                    y el horario que mejor se adapte a tu día. Todo a un clic de distancia.
                </p>
                <div class="booking-cta-wrap reveal reveal-delay-3" style="margin-top: 1rem; margin-bottom: 2rem;">
                    <a href="../views/auth/login.php" class="btn btn-gold" style="padding: 20px 48px; font-size: 1rem;">
                        <span>Reservar Cita Ahora</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <div class="booking-info">
                <div class="booking-info-item reveal reveal-delay-3">
                    <div class="booking-info-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div>
                        <div class="booking-info-label">Ubicación</div>
                        <div class="booking-info-value">Carrera 2</div>
                        <div class="booking-info-sub">A 5 minutos del parque de los niños</div>
                    </div>
                </div>
                <div class="booking-info-item reveal reveal-delay-4">
                    <div class="booking-info-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div class="booking-info-label">Horario</div>
                        <div class="booking-info-value">Lun – Sáb: 9:00 AM – 8:00 PM</div>
                    </div>
                </div>
                <div class="booking-info-item reveal reveal-delay-5">
                    <div class="booking-info-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="booking-info-label">Contacto</div>
                        <div class="booking-info-value">+57 313 876 3227</div>
                        <div class="booking-info-sub">mauriciogym2023@gmail.com</div>
                    </div>
                </div>
        </div>
    </div>
</section>
                
<section id="gallery-section">
    <div class="container">
        <div class="gallery-header">
            <div class="section-label reveal" style="justify-content:center;"><span class="gold-line"></span>Nuestro Trabajo<span class="gold-line"></span></div>
            <h2 class="section-title reveal reveal-delay-1">Excelencia en<br><em>Cada Detalle</em></h2>
        </div>
        <div id="gallery" aria-label="Galería MC Barber">
            <?php
            $galleryImgs = [
                ['src'=>'img/gallery_main.jpg',  'alt'=>'Interior premium de MC Barber'],
                ['src'=>'img/gallery_fade.jpg',  'alt'=>'Corte fade con tijera'],
                ['src'=>'img/service_3.jpg', 'alt'=>'Estilo masculino y cuidado de barba'],
                ['src'=>'img/service_2.jpg', 'alt'=>'Afeitado clásico con navaja y toalla caliente'],
            ];
            foreach ($galleryImgs as $gi):
            ?>
            <div class="gallery-item reveal reveal-delay-2">
                <img src="<?= htmlspecialchars($gi['src']) ?>" alt="<?= htmlspecialchars($gi['alt']) ?>" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<footer id="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand-logo">MC Barber</div>
                <div class="footer-brand-tagline">Barbería Premium &middot;2026</div>
                <p class="footer-brand-desc">El arte del estilo masculino elevado a su máxima expresión. Visítanos y descubre la diferencia de una barbería verdaderamente premium.</p>
                <div class="footer-social">
                    <a href="#" class="footer-social-btn" aria-label="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="#" class="footer-social-btn" aria-label="Facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    <a href="#" class="footer-social-btn" aria-label="Twitter/X">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
                    </a>
                </div>
            </div>

            <div>
                <div class="footer-col-title">Navegación</div>
                <ul class="footer-links">
                    <li><a href="#about"        class="footer-link">Nosotros</a></li>
                    <li><a href="#services"     class="footer-link">Servicios</a></li>
                    <li><a href="#team"         class="footer-link">Equipo</a></li>
                    <li><a href="#testimonials" class="footer-link">Reseñas</a></li>
                    <li><a href="../views/auth/login.php" class="footer-link">Reservar Cita</a></li>
                </ul>
            </div>

            <div>
                <div class="footer-col-title">Servicios</div>
                <ul class="footer-links">
                    <li><a href="#services" class="footer-link">Corte Clásico</a></li>
                    <li><a href="#services" class="footer-link">Degradado (Fade)</a></li>
                    <li><a href="#services" class="footer-link">Arreglo de Barba</a></li>
                    <li><a href="#services" class="footer-link">Afeitado con Navaja</a></li>
                </ul>
            </div>

            <div>
                <div class="footer-col-title">Contacto</div>
                <div class="footer-contact-item">
                    <div class="footer-contact-icon-wrap">
                        <svg class="footer-contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    </div>
                    <div class="footer-contact-text">Av. Circunvalar<br>Ciudad, Neiva Huila</div>
                </div>
                <div class="footer-contact-item">
                    <div class="footer-contact-icon-wrap">
                        <svg class="footer-contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="3" ry="3"></rect><path d="M12 18h.01"></path></svg>
                    </div>
                    <div class="footer-contact-text">+57 313 876 3227</div>
                </div>
                <div class="footer-contact-item">
                    <div class="footer-contact-icon-wrap">
                        <svg class="footer-contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><polyline points="3 7 12 13 21 7"></polyline></svg>
                    </div>
                    <div class="footer-contact-text">mauriciogym2023@gmail.com</div>
                </div>
                <div class="footer-contact-item">
                    <div class="footer-contact-icon-wrap">
                        <svg class="footer-contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div class="footer-contact-text">Lun–Sáb: 9:00 AM – 8:00 PM<br>Dom: Cerrado</div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-copy">&copy; <?= date('Y') ?> <span>MC Barber</span>. Todos los derechos reservados.</div>
            <div class="footer-bottom-links">
                <a href="#" class="footer-bottom-link">Privacidad</a>
                <a href="#" class="footer-bottom-link">Términos</a>
                <a href="#" class="footer-bottom-link">Cookies</a>
            </div>
        </div>
    </div>
</footer>

<script>

const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
}, { passive: true });

const hamburger = document.getElementById('hamburger');
const navMenu   = document.getElementById('navMenu');
let menuOpen    = false;
hamburger.addEventListener('click', () => {
    menuOpen = !menuOpen;
    navMenu.classList.toggle('open', menuOpen);
    hamburger.setAttribute('aria-expanded', String(menuOpen));
    const [s0, s1, s2] = hamburger.querySelectorAll('span');
    if (menuOpen) {
        s0.style.cssText = 'transform:translateY(6.5px) rotate(45deg)';
        s1.style.cssText = 'opacity:0;transform:scaleX(0)';
        s2.style.cssText = 'transform:translateY(-6.5px) rotate(-45deg)';
    } else {
        [s0, s1, s2].forEach(s => s.style.cssText = '');
    }
});
navMenu.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => {
        menuOpen = false;
        navMenu.classList.remove('open');
        hamburger.querySelectorAll('span').forEach(s => s.style.cssText = '');
        hamburger.setAttribute('aria-expanded', 'false');
    });
});

const revealObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('revealed');
            revealObserver.unobserve(e.target);
        }
    });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

const heroBg = document.querySelector('.hero-bg');
window.addEventListener('scroll', () => {
    if (window.scrollY < window.innerHeight)
        heroBg.style.transform = `scale(1.05) translateY(${window.scrollY * 0.3}px)`;
}, { passive: true });



const glow = Object.assign(document.createElement('div'), { style: `
    position:fixed;width:300px;height:300px;border-radius:50%;pointer-events:none;z-index:9997;
    background:radial-gradient(circle,rgba(201,168,76,0.06) 0%,transparent 65%);
    transform:translate(-50%,-50%);transition:left 0.2s ease,top 0.2s ease;opacity:0;
` });
document.body.appendChild(glow);

document.addEventListener('mousemove', e => {
    glow.style.left = e.clientX + 'px';
    glow.style.top  = e.clientY + 'px';
    glow.style.opacity = '1';
});
</script>

</body>
</html>
