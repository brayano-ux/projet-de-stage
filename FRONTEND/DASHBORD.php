<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Document</title>

    <style>
        :root{
            --bg: #f4f6fb;
            --surface: #ffffff;
            --muted: #6b7280;
            --primary: #2563eb;
            --accent-start: #334155;
            --accent-end: #475569;
            --card-radius: 14px;
            --shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        }

        *{
            box-sizing: border-box;
            outline: none;
        }

        html,body{
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .header {
            width: 100%;
            height: 72px;
            background-color: var(--surface);
            display: flex;
            align-items: center;
            padding: 0 24px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 6px 24px rgba(15,23,42,0.06);
            border-bottom: 1px solid rgba(15,23,42,0.04);
        }

        .entete {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .entete h3 {
            color: var(--primary);
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.2px;
        }

        .entete h3 i {
            font-size: 22px;
            color: var(--primary);
        }

        .autre {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .autre button {
            background-color: transparent;
            border: none;
            padding: 8px 12px;
            color: var(--muted);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform .18s ease, color .18s ease, background .18s ease;
        }

        .autre button:hover {
            background-color: rgba(37,99,235,0.06);
            color: var(--primary);
            transform: translateY(-3px);
        }

        .gauche {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            background-color: var(--surface);
            border: 1px solid rgba(15,23,42,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform .18s ease, background .18s ease;
        }

        .gauche button {
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: orangered;
        }

        .gauche:hover {
            transform: translateY(-2px);
        }

        .gauche:hover i {
            color: white;
        }

        .but {
            background-color: var(--surface);
            border: 1px solid rgba(15,23,42,0.06);
            padding: 8px 14px;
            border-radius: 20px;
            font-weight: 600;
            color: #0f172a;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 14px;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .but:hover{
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .sidebar {
            width: 260px;
            height: calc(100vh - 72px);
            background: linear-gradient(180deg, var(--accent-start), var(--accent-end));
            padding: 22px 18px;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 72px;
            left: 0;
            overflow-y: auto;
            z-index: 900;
            box-shadow: 2px 6px 20px rgba(15,23,42,0.06);
            border-right: 1px solid rgba(255,255,255,0.04);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 10px;
        }

        .nav-section {
            display: flex;
            flex-direction: column;
        }

        .nav-title {
            font-size: 18px;
            color: #dbe7f7;
            margin: 10px 0;
        }

        .nav-section a {
            color: #e2e8f0;
            padding: 10px;
            text-decoration: none;
            display: flex;
            gap: 10px;
            border-radius: 8px;
            transition: background 0.3s;
            align-items: center;
        }

        .nav-section a:hover {
            background-color: rgba(255,255,255,0.04);
        }

        .autre_coter {
            margin-left: 260px;
            position: relative;
            left: 50px;
            top: 100px;
            padding: 28px;
            border-radius: 14px;
            width: 1050px;
            background: var(--surface);
            border: 1px solid rgba(15,23,42,0.04);
            box-shadow: var(--shadow);
            color: #0f172a;
        }

        .boutique-info h1 {
            font-size: 26px;
            color: #0f172a;
            margin: 0 0 6px 0;
        }

        .boutique-meta {
            color: var(--muted);
            margin-bottom: 12px;
            font-size: 14px;
        }

        .boutique-actions .btn-primary {
            background-color: var(--primary);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            color: white;
            padding: 10px 18px;
            font-size: 14px;
            margin-right: 12px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(37,99,235,0.12);
            transition: transform .18s ease;
        }

        .boutique-actions .btn-primary:hover{ transform: translateY(-3px); }

        .boutique-actions .btn-outline {
            background: transparent;
            border: 1px solid rgba(15,23,42,0.06);
            border-radius: 10px;
            font-weight: 600;
            color: var(--muted);
            padding: 10px 18px;
            font-size: 14px;
            margin-right: 12px;
            cursor: pointer;
        }
        .premuinm{
            color: white;
            background-color: var(--primary);
            padding: 14px;
            border-radius: 12px;
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .premuinm button{
            background-color: white;
            border:none;
            padding: 8px 14px;
            color: var(--primary);
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
        }
        .parametre{
            position: relative;
            top: 120px;
            margin-left: 260px;
            left: 50px;
            width: 1050px;
            padding: 20px;
        }

        .avancer {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            align-items: stretch;
        }

        .eye, .jaime, .visite {
            background: var(--surface);
            padding: 22px;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow);
            min-width: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .eye:hover, .jaime:hover, .visite:hover{ transform: translateY(-6px); box-shadow: 0 12px 30px rgba(15,23,42,0.12); }

        .eye i {
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .jaime i { font-size: 28px; color: #ef4444; margin-bottom: 10px; }
        .visite i { font-size: 28px; color: #8b5cf6; margin-bottom: 10px; }

        .eye span, .jaime span, .visite span { display: block; margin: 6px 0; }

        .eye span:first-of-type, .jaime span:first-of-type, .visite span:first-of-type {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .eye span:last-of-type, .jaime span:last-of-type, .visite span:last-of-type {
            font-size: 13px;
            color: var(--muted);
        }

        @media (max-width: 1200px){
            .autre_coter{ width: calc(100% - 320px); left: 20px; }
            .parametre{ width: calc(100% - 320px); left: 20px; }
        }

        @media (max-width: 900px){
            .sidebar{ transform: translateX(-100%); position: fixed; z-index: 10000; }
            .autre_coter, .parametre{ margin-left: 20px; left: 0; width: auto; top: 90px; }
            .entete h3{ font-size: 18px }
            .autre{ display: none; }
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="entete">
            <h3><i class="fas fa-envelope"></i> Creator Market</h3>

            <div class="autre">
                <button><i class="fas fa-home"></i> Accueil</button>
                <button><i class="fas fa-search"></i> Marketplace</button>
                <button><i class="fas fa-user"></i> Dashboard</button>
                <button><i class="fas fa-crown"></i> Créer</button>
            </div>

            <div style="display:flex; align-items:center;">
                <div class="gauche">
                    <button><i class="fa-solid fa-moon"></i></button>
                </div>
                <button class="but">
                    <i class="fa-solid fa-globe"></i> FR
                </button>
            </div>
        </div>
    </div>

    <div class="sidebar">
        <div class="nav-section">
            <div class="nav-title">Principal</div>
            <a href="#"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="#"><i class="fas fa-store"></i><span>Ma Boutique</span></a>
            <a href="#"><i class="fas fa-box"></i><span>Produits</span></a>
            <a href="#"><i class="fas fa-shopping-cart"></i><span>Commandes</span></a>

            <div class="nav-title">Marketing</div>
            <a href="#"><i class="fas fa-chart-line"></i><span>Analytics</span></a>
            <a href="#"><i class="fas fa-bullhorn"></i><span>Promotions</span></a>

            <div class="nav-title">Paramètres</div>
            <a href="#"><i class="fas fa-cog"></i><span>Paramètres</span></a>
            <a href="#"><i class="fas fa-user"></i><span>Profil</span></a>
            <a href="#"><i class="fas fa-question-circle"></i><span>Aide & Support</span></a>
             <div class="augmenter">
                <div class="premuinm">
            <h4>Passez à Premium</h4>
            <p style="font-size: 14px; margin-bottom: 15px;">Obtenez 2x plus de clients</p>
            <button class="upgrade-btn">
                <i class="fas fa-crown"></i>
                <span>Découvrir Premium</span>
            </button>
            </div>
        </div>
        </div>
    </div>

    <div class="autre_coter">
        <div class="boutique-info">
            <h1>Ma Boutique</h1>

            <div class="boutique-meta">
                <i class="fas fa-calendar"></i> Créée le 15 mars 2024 •
                <i class="fas fa-star"></i> Boutique Active
            </div>

            <div class="boutique-actions">
                <button class="btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </button>

                <button class="btn-outline">
                    <i class="fas fa-eye"></i> Voir ma boutique
                </button>

                <button class="btn-outline">
                    <i class="fas fa-share-alt"></i> Partager
                </button>
            </div>
        </div>
        
    </div>
    <div class="parametre">
           <div class="avancer">
            <div class="eye">
                <i class="fas fa-eye"></i>
                <span>1,345 Vues</span>
                <span>Total de vues</span>

            </div>
             <div class="visite">
                <i class="fa-solid fa-users"></i>
                <span>843</span>
                <span>Visiteurs uniques</span>

            </div>
             <div class="jaime">
<i class="fa-solid fa-heart"></i>                <span>843</span>
                <span style="color: white;">J'aime recues</span>

            </div>

           </div>
        </div>

</body>
</html>
