<?php
session_start();
require_once __DIR__ . '/config/index.php';

$isLoggedIn = isset($_SESSION['user_id']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
 <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script></head>
    <link rel="stylesheet" href="src/css/acceuil.css">
    <style>
        
        .conteneur-footer {
            max-width: 1200px;
            margin: 0 auto; 
            padding: 0 20px;
        }

        .contenu-footer {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .section-footer {
            text-align: left;
        }

        .titre-section {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #3498db;
        }

        .liste-liens {
            list-style: none;
        }

        .liste-liens li {
            margin-bottom: 12px;
        }

        .lien-footer {
            color: #bdc3c7;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .lien-footer:hover {
            color: #3498db;
        }

        .info-contact {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 16px;
            color: #bdc3c7;
        }

        .info-contact i {
            margin-right: 10px;
            color: #3498db;
            width: 20px;
        }

        .reseaux-sociaux {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .bouton-reseau {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background-color: #34495e;
            border-radius: 50%;
            color: white;
            font-size: 18px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .bouton-reseau:hover {
            background-color: #3498db;
            transform: translateY(-3px);
        }

        .formulaire-newsletter {
            margin-top: 20px;
        }

        .champ-email {
            width: 100%;
            max-width: 300px;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .bouton-newsletter {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .bouton-newsletter:hover {
            background-color: #2980b9;
        }

        .bas-footer {
            border-top: 1px solid #34495e;
            padding: 25px 0;
            text-align: center;
            background-color: #1a252f;
        }

        [theme="dark"] .bas-footer {
            background-color: #0f0f0f;
        }

        .texte-copyright {
            color: #95a5a6;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .text{
            position: relative;
            left: 35%;
        }

        .liens-legaux {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .lien-legal {
            color: #95a5a6;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .lien-legal:hover {
            color: #3498db;
        }

        @media (max-width: 768px) {
            .contenu-footer {
                grid-template-columns: 1fr;
                gap: 30px;
            }
.milieu{
    width: 100%;
    min-height: 100vh;
}
            .section-footer {
                text-align: center;
            }
.text{
    width: 100%!important;
}
            .reseaux-sociaux {
                justify-content: center;
            }

            .liens-legaux {
                flex-direction: column;
                gap: 15px;
            }
            .categorie{
                position: relative;
                left: 0px!important;
            }
            .entete{
                position: relative;
                left: -10px !important;
            }
            .container{
                max-width: 90%;
                width: 100%;
                padding: 0px 15px;
                margin:  0 auto;
                box-sizing: border-box;
            }
            .client{
                display: flex !important;
                flex-wrap: wrap !important;
            }

            .champ-email {
                max-width: 100%;
            }
            .le{
                display: blo;
            }
           
        }
 #ceula{
                display: none;
            }
        @media (max-width: 480px) {
            .pied-page {
                padding: 30px 0 0 0;
            }

            .conteneur-footer {
                padding: 0 15px;
            }

            .titre-section {
                font-size: 18px;
            }

            .lien-footer {
                font-size: 15px;
            }
            #ceulala{
                display: none;
                opacity: 0;
            }
        }
        .h5{
            padding: 10px;
        }
     .precreator h1 i {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  width: 100px;
  height: 100px;
  font-size: 40px;
  border-radius: 50%;
  color: white;
  background: linear-gradient(135deg, #ff7e5f, #feb47b);
  transition: transform 0.3s, background 0.3s;
  margin-bottom: 15px;
}
.precreator:hover{
transform: translateY(-2px);
transform: scale(1.1);
transition: 0.3s ease-in-out;
}
        .ia {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        #robot {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            font-size: 22px;
            padding: 16px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 3px solid white;
        }

        #robot:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(59, 130, 246, 0.4);
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        @keyframes pulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
            }
            50% { 
                transform: scale(1.02);
                box-shadow: 0 10px 36px rgba(59, 130, 246, 0.4);
            }
        }

        #div {
            position: fixed;
            bottom: 100px;
            right: 30px;
            z-index: 999;
        }

        .chatbot {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255, 255, 255, 0.05);
            width: 380px;
            max-height: 580px;
            overflow: hidden;
            transform: scale(0);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: slideIn 0.5s ease forwards;
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
        }

        @keyframes slideIn {
            to { 
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .debut {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--card-bg) 100%);
            color: var(--text-color);
            padding: 24px 24px 20px 24px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }

        .debut h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
            line-height: 1.4;
        }

        .debut::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 2px;
        }

        .milieu {
            padding: 24px;
            background: var(--card-bg);
            background-image: url('c:\Users\lenovo\Pictures\file_000000005fa061f6a175377cd8c0ce3b.png');
            background-size: cover;
            background-position: center;

        }

        .conversation {
            max-height: 280px;
            overflow-y: auto;
            height: 300px;
            margin-bottom: 20px;
            padding: 16px;
            background: var(--bg-color);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .conversation::-webkit-scrollbar {
            width: 6px;
        }

        .conversation::-webkit-scrollbar-track {
            background: transparent;
        }

        .conversation::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .conversation::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .message-user {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            padding: 12px 16px;
            border-radius: 20px 20px 6px 20px;
            margin: 12px 0;
            margin-left: 40px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 12px rgba(59, 130, 246, 0.25);
            position: relative;
            animation: messageSlideIn 0.3s ease;
        }

        .message-bot {
            background: linear-gradient(135deg, var(--card-bg), var(--card-bg));
            color: var(--text-color);
            padding: 12px 16px;
            border-radius: 20px 20px 20px 6px;
            margin: 12px 0;
            margin-right: 40px;
            font-size: 14px;
            font-weight: 400;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            position: relative;
            animation: messageSlideIn 0.3s ease;
        }

        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .input-zone {
            display: flex;
            gap: 12px;
            position: relative;
            align-items: center;
        }

        #question {
            flex: 1;
            padding: 14px 18px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--card-bg);
            color: var(--text-color);
        }

        #question:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: var(--card-bg);
        }

        #question::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        #envoyer {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        #envoyer:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        #envoyer:active {
            transform: translateY(0);
        }

        .fermer {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            border: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 18px;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 400;
        }

        .fermer:hover {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #dc2626;
            border-color: #fca5a5;
            transform: scale(1.05);
        }
        .envoie{
            background-color: lavender;
            border-radius: 20px;
            padding: 15px;
            position: relative;
            bottom: 10px;
            font-size: 17px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 
            Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            font-weight: bold;
            text-align: center;
        }
        .le{
            display: none;
        }
        .envoie:hover{
            transform: scale(1.05);
            background: rgba(0, 0, 255, 0.568);
            border: 2px solid white;
        }
        .essai{
justify-content: baseline;        }
[theme="dark"] .h2{
    color: white;
}
.marcher:hover{
    background-color: hsl(210, 47%, 87%);
    padding: 10px;
    border-radius: 15px;
    font-size: 17px;
    font-weight: bold;
    transform: translateY(-3px);
    color:gray;
    

}
  :root {
            --bg-color: white;
            --text-color: #333;
            --card-bg: white;
            --border-color: #e2e8f0;
            --header-bg: white;
            --footer-bg: #2c3e50fb;
        }

[theme="dark"]  {
           --bg-color: #1a1a1a;
            --text-color: #e5e5e5;
            --card-bg: #2d2d2d;
            --border-color: #444;
            --header-bg: #2d2d2d;
            --footer-bg: #1a1a1a;
        }

         .entete {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, 
                Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            font-size: 25px;
            font-weight: bold;
            color: rgb(255, 84, 4);
            position: relative; 
            left: 2%;
        }

        * {
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            box-sizing: border-box;
            padding: 0;
            overflow-x: hidden;
            align-items: center;
            justify-content: center;
            margin: 0;
            height: 100%;
            width: 100%;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .titre {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            padding: 10px;
            gap: 20px;
            opacity: 1;
            z-index: 999;
            background-color: var();
            display: flex;
            justify-content: space-around;
            align-items: center;
            position: fixed;
            top: 0px;
            width: 100%;
            left: 0px;
            transition: background-color 0.3s ease;
        }

        button {
            border: none;
            background: transparent;
            font-size: 17px;
            margin: 20px;
            color: rgba(0, 0, 0, 0.699);
            cursor: pointer;
        }

[theme="dark"] button{
        color: rgba(255, 255, 255, 0.7);
        }

        .autre {
            justify-content: space-around;
            display: flex;
            align-items: center;
            position: relative;
            left: -10%;
        }

        .categorie {
            position: relative;
            left: 20%;
            display: flex;
            align-items: center;
        }
        .menu-hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
        }
        .ceux{
            position: relative;left: 10%;
        }

        .menu-hamburger div {
            width: 25px;
            height: 3px;
            background-color: rgb(255, 84, 4);
            margin: 3px 0;
            transition: 0.3s;
        }

        .menu-hamburger.ouvert div:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        .menu-hamburger.ouvert div:nth-child(2) {
            opacity: 0;
        }
        .menu-hamburger.ouvert div:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        .mode {
            background: transparent;
            border: 2px solid rgb(255, 84, 4);
            color: rgb(255, 84, 4);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            margin: 0 10px;
        }

        .mode:hover {
            background: rgb(255, 84, 4);
            color: white;
            transform: scale(1.04);
            transform: translateY(3px);
        }

        @media (min-width: 769px) {
            .ceux {
                display: flex;
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .entete {
                position: relative;
                left: 0%;
                font-size: 16px;
                align-items: center;
                justify-content: center;
            }

            .ceux {
                display: none;
                opacity: 0;
                position: fixed;
                top: 57px;
                left: 0%;
                width: 50%;
                height: calc(50vh - 70px);
                background-color: var(--header-bg);
                flex-direction: column;
                justify-content: flex-start;
                padding-top: 50px;
                z-index: 998;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
                transition: background-color 0.3s ease;
            }
            .entete{
                position: relative;
                left: -15%!important;
                font-size: 18px!important;
            }
            .ceux.menu-ouvert {
                display: flex !important;
                opacity: 1 !important;
            }
            .ceux.menu-ouvert button {
                width: 80%;
                max-width: 250px;
                padding: 10px;
                border: none;
                margin: 10px ;
                font-size: 17px;
                color: var(--text-color);
                background-color: transparent;
                border-radius: 10px;
            }
            .notification{
                margin: 4px;
                gap: 40px;
            }
            .tit{
                font-size: 30px;
                padding: 10px;
            }
            .text{
                top: -30px;
                width: 80%!important;
                position: relative;
                border-radius: 15px!important;
                left: 10px !important;
            }
#commence{
    font-size: 18px!important;
    width: 80%!important;
}
.ligne{
    position: relative;
    left: -30%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center
;
}
.h2{
padding: 10px;
}
.tect{
    height: 1000px;
}
/* #span{
    padding: 2px;
} */

.client{
    position: relative;
    top: -30px;
    display: flex;
    flex-wrap: wrap;
    text-align: center;
    padding: 30px;
    
}
            .ceux.menu-ouvert button:hover {
                background-color: #ffe8e0;
                color: rgb(255, 84, 4);
            }
                        .menu-hamburger {
                display: flex;
                font-size: 10px!important;
                color:blue !important;
            }
                        .autre {
                justify-content: space-between;
                left: 0;
                width: 100%;
                padding: 0 20px;
            }
.entete{
    position: relative;
    left: -80px;
}
            .categorie {
                position: relative !important;
                left:-100px!important;
            }
            .menu-hamburger{
                position: relative;
                left: -15px;
                font-size: 5px!important;
            }
        }
      
    .text {
        position: relative;
        /* left: 40%; */
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: linear-gradient(135deg, lavender, #ffe4b5);
      color: rgb(255, 84, 4);
      font-size: 1.2rem;
      font-weight: bold;
      width: 30%;
      padding: 15px 25px;
      border-radius: 30px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      text-align: center;
      animation: bouge 1.2s ease-in-out;
      cursor: default;
    }

    .text i {
      font-size: 1.5rem;
      color: goldenrod;
      animation: bounce 1.5s infinite;
    }

    @keyframes bouge {
      from {opacity: 0; transform: translateY(-20px);}
      to {opacity: 1; transform: translateY(0);}
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0);}
      50% { transform: translateY(-5px);}
    }

    .milieu {
      text-align: center;
      background-color: #ffe4b5;
      height: 800px;
      background: var(--bg-color);
      transition: background-color 0.3s ease;
        background-image: url('c:\Users\lenovo\Pictures\file_000000005fa061f6a175377cd8c0ce3b.png');
            background-size: cover;
            background-position: center;
    }

    [theme="dark"] .milieu {
        background: var(--bg-color);
    }

    h1{
        font-size: 75px;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, 
        Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        font-weight: bold;
        text-align: center;
        color: var(--text-color);
    }
    #commence{
        background-color: rgb(255, 84, 4);
         position: relative;
        left: 30%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
color: white;
      font-size: 1.2rem;
      font-weight: bold;
      width: 25%;
      height: 60px;
      padding: 15px 25px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
      text-align: center;
      animation: bouge 1.2s ease-in-out;
      cursor: default;

    }
    #commence:hover{
        transform: translateY(-4px);
        transition: 1s ease-in-out;
        transform: scale(1.1);
    }
    .ligne{
        display: flex;
        align-items: center;
    }
    #span{
        position: relative;left: 30%;
        font-size: 17px;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
         Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
         font-weight: bold;
         color: var(--text-color);
         text-decoration: none;
    }
    p{
        font-size: 18px;
        font-weight: 500;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI',
         Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
         color: rgba(0, 0, 0, 0.644);
    }

    [theme="dark"] p {
        color: rgba(255, 255, 255, 0.7);
    }

    .h2{
        color:rgb(255, 84, 4) ;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, 
        Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        font-weight: bold;
        font-size: 25px;
    }
    .p{
        font-size: 17px;
        font-weight: bold;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
         Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }
    .client{
        padding: 10px!important;
        display: flex;
        align-items: center;
        justify-content: space-around;
    }
    .creator{
        display: flex;
        flex-wrap: wrap;
        text-align: center;
        padding: 30px;
        font-size: 30px;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
         Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
         font-weight: bold;
    }
    .solution{
        text-align: center;
    }
.precreator{
    background: var(--card-bg);
    border-radius: 20px;
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
          width: 300px;
          padding: 50px;
          justify-content: center;
          align-items: center;
          text-align: center;
margin: 10px;
transition: background-color 0.3s ease;
}
.creator{
    display: flex;
    justify-content: space-around;
}
.co-template{
    background:transparent;
    border-radius: 20px;
          width: 300px;
          padding: 50px;
          justify-content: center;
          align-items: center;
          text-align: center;
margin: 10px; 
}
.dessous{
    text-align: center;
}
.marche{
    background-color: #f7f2ea;
    padding: 20px;
}

[data-theme="dark"] .marche {
    background-color: #2a2a2a;
}

.steps {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 20px;
  margin: 40px auto;
            background: linear-gradient(135deg, rgba(230, 230, 250, 0.3) 0%, rgba(220, 240, 255, 0.2) 100%);
  width: 100%;
  padding: 20px;
}

[theme="dark"] .steps {
    background-color: #333;
}

.co-template {
  text-align: center;
  padding: 20px;
  border-radius: 15px;
  width: 500px;
}

.co-template .i1 {
  width: 45px;
  height: 45px;
  margin: auto;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgb(255, 84, 4);
  color: white;
  font-size: 18px;
  font-weight: bold;
  border-radius: 50%;
  margin-bottom: 15px;
}

.co-template h2 {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 10px;
  color: var(--text-color);
}

.co-template p {
  font-size: 15px;
  color: var(--text-color);
  line-height: 1.6;
}

@media (max-width: 768px) {
  .steps {
    flex-direction: column;
    align-items: center;
  }
  .co-template {
    width: 100%;
    max-width: 350px;
  }
}
.ma{
    text-align: center;
    padding: 20px;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
     Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
     font-weight: bold;
}

        .contenu-principal {
            padding: 50px 20px;
            text-align: center;
            min-height: 80vh;
        }

.pied-page {
    background: linear-gradient(125deg, #299e27 0%, #5a63e0 100%);
    color: #ffffff;
    margin-bottom: 20px;
    height: 500px;
    width: 100%;
    font-size: 18px;
    padding: 50px  ;
    margin-top: auto;
    transition: background 0.3s ease, color 0.3s ease;
    text-align: center;
}.sat i{
    font-size: 30px; 
    background-color: #b8dfc1;
        width: 60px; 
    height: 60px;
    border-radius: 50%;
        display: inline-flex; 
    align-items: center;
    justify-content: center;
    
    margin-bottom: 15px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08); 
        transition: all 0.3s ease; 
}

.fcfa i{
    font-size: 30px; 
    background-color: #b8d6df;
    color: rgb(99, 99, 173);
        width: 60px; 
    height: 60px;
    border-radius: 50%;
        display: inline-flex; 
    align-items: center;
    justify-content: center;
    
    margin-bottom: 25px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08); 
        transition: all 0.3s ease; 
}
.comm i{
    font-size: 30px; 
    background-color: #9b360f;
    color: rgb(245, 247, 248);
        width: 60px; 
    height: 60px;
    border-radius: 50%;
        display: inline-flex; 
    align-items: center;
    justify-content: center;
    
    margin-bottom: 25px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08); 
        transition: all 0.3s ease; 
}
.crea i{
    font-size: 30px; 
    background-color: #b8d6df;
    color: rgb(173, 156, 99);
        width: 60px; 
    height: 60px;
    border-radius: 50%;
        display: inline-flex; 
    align-items: center;
    justify-content: center;
    
    margin-bottom: 25px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08); 
        transition: all 0.3s ease; 
}

* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .amelioration {
            padding: 80px 40px;
            min-height: 600px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }

        .separe {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 60px;
            max-width: 1200px;
            width: 100%;
            flex-wrap: wrap;
        }

        .uncoter {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .uncoter img {
            object-fit: cover;
            height: 500px;
            width: 700px;
            max-width: 100%;
            display: block;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .uncoter:hover img {
            transform: scale(1.08);
        }

        .uncoter::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(0,115,230,0.1), transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .uncoter:hover::after {
            opacity: 1;
        }

        .autrecoter {
            max-width: 450px;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .autrecoter h2 {
            font-size: 38px;
            color: #1a1a1a;
            margin-bottom: 20px;
            font-weight: 700;
            line-height: 1.2;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .autrecoter p {
            font-size: 18px;
            color: #444;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .autrecoter strong {
            color: #0073e6;
            font-weight: 700;
            font-size: 20px;
        }

        .cta {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #66eab3ff 0%, #4ba288ff 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '🔥';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.2);
            transition: left 0.5s ease;
        }

        .cta:hover::before {
            left: 100%;
        }

        .cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }

        .cta:active {
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .amelioration {
                padding: 40px 20px;
                min-height: auto;
            }

            .separe {
                gap: 30px;
            }

            .uncoter img {
                height: 350px;
                width: 100%;
            }

            .autrecoter h2 {
                font-size: 28px;
            }

            .autrecoter p {
                font-size: 16px;
            }

            .cta {
                padding: 14px 30px;
                font-size: 15px;
            }
        } .frequente {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(135deg, rgba(230, 230, 250, 0.3) 0%, rgba(220, 240, 255, 0.2) 100%);
            min-height: 100vh;
        }

        .tous_contenu {
            max-width: 1200px;
            margin: 0 auto;
        }

        .title {
            margin-bottom: 60px;
            animation: fadeInDown 0.8s ease-out;
        }

        .question {
            font-size: 48px;
            font-weight: 800;
            color: #1e1e2f;
            margin-bottom: 15px;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #1e1e2f 0%, #5c63ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .reponse {
            font-size: 20px;
            color: #666;
            font-weight: 400;
            line-height: 1.5;
        }

        .faq {
            max-width: 800px;
            margin: 0 auto 24px auto;
            text-align: left;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .faq:nth-child(2) { animation-delay: 0.1s; }
        .faq:nth-child(3) { animation-delay: 0.2s; }
        .faq:nth-child(4) { animation-delay: 0.3s; }
        .faq:nth-child(5) { animation-delay: 0.4s; }

        .faq-question {
            background: white;
            padding: 24px 30px;
            font-size: 18px;
            font-weight: 600;
            color: #1e1e2f;
            border-radius: 16px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.06);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .faq-question::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #5c63ff 0%, #7c83ff 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .faq-question:hover {
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            transform: translateX(4px);
            box-shadow: 0 6px 30px rgba(92, 99, 255, 0.15);
            border-color: rgba(92, 99, 255, 0.1);
        }

        .faq-question:hover::before {
            transform: scaleY(1);
        }

        .faq-question span {
            flex: 1;
            padding-right: 20px;
        }

        .faq-question i {
            color: #5c63ff;
            font-size: 16px;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 20px;
            text-align: center;
        }

        .faq-reponse {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            background: white;
            border-radius: 0 0 16px 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: top;
            margin-top: -16px;
            padding: 0 30px;
        }

        .faq-reponse p {
            padding: 0;
            margin: 20px 0;
            color: #555;
            font-size: 17px;
            line-height: 1.8;
            font-weight: 400;
        }

        .faq.active .faq-question {
            background: linear-gradient(135deg, #5c63ff 0%, #7c83ff 100%);
            color: white;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 6px 30px rgba(92, 99, 255, 0.25);
        }

        .faq.active .faq-question i {
            transform: rotate(180deg);
            color: white;
        }

        .faq.active .faq-reponse {
            max-height: 600px;
            opacity: 1;
            padding: 20px 30px 30px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .faq.desactive .faq-reponse {
            display: none;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .frequente {
                padding: 60px 16px;
            }

            .title {
                margin-bottom: 40px;
            }

            .question {
                font-size: 36px;
            }

            .reponse {
                font-size: 18px;
            }

            .faq {
                margin-bottom: 16px;
            }

            .faq-question {
                font-size: 16px;
                padding: 20px 24px;
            }

            .faq-question:hover {
                transform: translateX(2px);
            }

            .faq-reponse p {
                font-size: 15px;
                line-height: 1.7;
            }
        }

        @media (max-width: 480px) {
            .question {
                font-size: 28px;
            }

            .reponse {
                font-size: 16px;
            }

            .faq-question {
                padding: 18px 20px;
                font-size: 15px;
            }

            .faq-reponse {
                padding: 0 20px;
            }

            .faq.active .faq-reponse {
                padding: 16px 20px 24px 20px;
            }

            .faq-reponse p {
                font-size: 14px;
                margin: 16px 0;
            }
        }
 .avant_footer{
        background: linear-gradient(125deg 100%  #1eaf56 , 50%#23af1e );
        height: 50px;
        width: 500px;
    }
    .pied-page h2{
        font-size: 50px;
        font-weight: bold;
       padding: 30px;
    }
    .pied-page p{
        color: rgba(255, 255, 255, 0.904);
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, 
        Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        font-size: 22px;
    }
    .pied-page{
        padding: 5px;
        width: 100%;
    }
    .pied-page #store{
        font-size: 30px;
        padding: 20px;
        width: 60px;
        height: 40px;
        text-align: center;
        justify-content: center;
        border-radius: 50%;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.226);
    }
    .conteneur_boutton{
        display: flex;
        align-items: center;
        text-align: center;
        position: relative;left: 30%;
    }
    .conteneur_boutton button{
padding: 20px;
background-color: white;
border-radius: 10px;
    }
    .ensemble{
        display: flex;
        align-items: center;
        gap: 40px;
        position: relative;left: 12%;
        padding: 20px;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, 
        Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        font-size: 17px;
        color: rgba(255, 255, 255, 0.829);
    }
    .client{
        background-color: #e4e7e9!important;
        padding: 20px;

    }
    @media (max-width: 768px) {
        .pied-page{
        padding: 30px;
         width: 100%;
         height: 100%;
            flex-wrap: wrap;
            position: relative;left: 0%;
            position: relative;left: 0%;
        }
        .conteneur_boutton{
            flex-direction: column;
            position: relative;left: 0%;
        }
        .pub{
            position: relative;
            display: flex;
            flex-wrap: wrap;
            margin: -10px;
        }
    }

    </style>
</head>
<div class="container">
<header>
    <div class="titre">
        <div class="autre">
             <div class="menu-hamburger" onclick="hamburger()">
                <div></div>
                <div></div>
                <div></div>
            </div>
            <blockquote  style="color: blue" class="entete">
                <i class="fas fa-envelope" ></i> 
                <span id="entete" data-lang-en="Creator Market">Creator market</span>
            </blockquote>
            <div class="ceux" id="ceux">
                <button id="acceuil" onclick="window.location.href='acceuil.php'" class="acceuil">
                    <i class="fas fa-envelope"></i> 
                    <span data-lang-en="Home">Acceuil</span>
                </button>
                <button onclick="window.location.href='marcher.php'" class="marcher">
                    <i class="fas fa-search"></i> 
                    <span data-lang-en="Market Place">Market Place</span>
                </button>
                <button onclick="window.location.href='templates.html'" class="marcher">
                    <i class="fas fa-crown"></i> 
                    <span data-lang-en="Create" onclick="allerMarketplace()">Créer</span>
                <button class="marcher">
                    <i class="fas fa-user"></i> 
                    <span data-lang-en="Dashboard" onclick="allerdashboard()">Tableau de bord</span>
                </button>
                <?php
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
?>
<script>
function allerdashboard() {
    let userId = <?php echo json_encode($user_id); ?>;
    if (!userId) {
        Swal.fire({
            icon: 'warning',
            title: 'Connexion requise',
            text: 'Vous devez être connecté pour accéder au dashboard.',
            confirmButtonText: 'Se connecter',
            confirmButtonColor: 'rgb(255, 84, 4)',
            showCancelButton: true,
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'INSCRIPTION.php';
            }
        });
    } else {
        window.location.href = 'dashboard.php';
    }
}
document.querySelector('.marcher').addEventListener('click',function(){
    allerdashboard();
})
</script>    
            </div>

            <div id="categorie" class="categorie">
                <button id="langue" class="notification">
                    <i class="fas fa-globe"></i> 
                    <span data-lang-en="EN">FR</span>
                </button>
                <button id="lemode" onclick="lumiositer()" class="mode" title="Mode sombre">
                    <i class="fas fa-moon"></i>
                </button>
                <div  id="ceulala">
                <?php if (!$isLoggedIn): ?>
                <button id="inscrire" onclick="window.location.href='inscription.html'"><i class="fas fa-sign-in-alt"></i> Inscription</button>
                <?php else: ?>
                <button id="deconnecter" onclick="logout()" title="Déconnexion"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
                <?php endif; ?>
                </div>
                
                <script>
                function logout() {
                    Swal.fire({
                        icon: 'question',
                        title: 'Confirmation',
                        text: 'Êtes-vous sûr de vouloir vous déconnecter ?',
                        confirmButtonText: 'Oui, déconnecter',
                        confirmButtonColor: 'rgb(255, 84, 4)',
                        showCancelButton: true,
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'logout.php';
                        }
                    });
                }
                </script>
            </div>
            
        </div>
    </div>
</header>

<div class="milieu">
    <div class="tect" style="position:relative;top: 150px;">
        <div class="text"> 
            <i class="fas fa-crown"></i> 
            <span data-lang-en="#1 African Creators Market">#1 Market place Createurs afrique</span>
        </div>
        <div class="essai">
        <h1 class="tit" style="padding: 10px;">
            <span data-lang-en="Create your Mini-Shop in 10 minutes">
                Crer votre <font color="rgb(255, 84, 4)"> Mini-Boutique <br></font>en 10 minutes
            </span>
        </h1>
        <p data-lang-en="The smart platform that allows any African creator, merchant or entrepreneur to launch their business online. QR Code, WhatsApp, Mobile Money - everything is included!">
            La plateforme intelligente qui permet 
           <br> à tout créateur, commerçant <br> ou entrepreneur africain de lancer son business en ligne. QR Code, WhatsApp, Mobile Money - tout est <br> inclus!
        </p>
        </div>
        <div class="ligne">
            <button id="commence" onclick="window.location.href='templates.html'" data-lang-en="Start for free">
                Commencer gratuitement <i class="fas fa-arrow-right"></i>
            </button>
            <a id="span" href="#" data-lang-en="Watch a Demo (2 min)">
                <i class="fas fa-video"></i> Voir une Demo (2 min)
            </a>
        </div>
        <br>
      <div class="client">
    <div class="crea">
        <i class="fa-solid fa-pen-nib"></i> <h2 class="h2" data-lang-en="10K +">10K +</h2> 
        <p class="p" data-lang-en="Active Creators">Créateurs actifs</p>
    </div>
    <div class="comm">
        <i class="fa-solid fa-box-open"></i> <h2 class="h2" data-lang-en="5000K +">5000K +</h2> 
        <p class="p" data-lang-en="Orders Processed">Commandes traitées</p>
    </div>
    <div class="fcfa">
        <i class="fa-solid fa-money-bill-wave"></i> <h2 class="h2" data-lang-en="50M +">50M +</h2> 
        <p class="p" data-lang-en="FCFA Generated">FCFA générés</p>
    </div>
    <div class="sat">
        <i class="fa-solid fa-users"></i>
        <h2 class="h2" data-lang-en="95%">95%</h2>
        <p class="p" data-lang-en="Customer Satisfaction">Satisfaction client</p>
    </div>
</div>
    </div>
</div>
    <section class="amelioration">
        <div class="separe">
            <div class="uncoter">
                <img src="https://img.freepik.com/photos-gratuite/influenceur-filmant-vlog-mode_482257-81586.jpg" alt="Influenceur filmant un vlog mode">
            </div>
            <div class="autrecoter">
                <h2>Rejoignez plus de 500 créateurs</h2>
                <p>Ils nous ont fait confiance.<br>
                À partir d'aujourd'hui, créez votre boutique en <strong>5 secondes</strong> !</p>
                <a href="#" class="cta">Créer ma boutique</a>
            </div>
        </div>
    </section>
<section>
    <div>
        <h2 class="creator" data-lang-en="Why Choose Creator Market?">Pourqoui Choisir Creator Market ?</h2>
        <p class="solution" data-lang-en="A complete solution designed for the African ecosystem with all the tools you need to succeed.">
            Une solution complète pensée pour l'écosystème africain avec tous les outils dont vous <br> avez besoin pour réussir.
        </p>
    </div>
</section>

<div class="creator">
    <div class="precreator">
        <h1><i class="fa-solid fa-qrcode"></i></h1>
        <h5 class="h5" data-lang-en="Instant QR Code">QR Code Instantané</h5>
        <p data-lang-en="Automatic generation of QR code and link for easy sharing">
            Génération automatique de QR code  et de lien pour <br> partage facile
        </p>
    </div>
    <div class="precreator">
        <h1><i class="fa-brands fa-whatsapp"></i></h1>
        <h5 class="h5" data-lang-en="Integrated WhatsApp">WhatsApp Intégré</h5>
        <p data-lang-en="Direct communication with your customers via WhatsApp">
            Communication directe avec vos clients via <br> WhatsApp
        </p>
    </div>
    <div class="precreator">
        <h1><i class="fa-solid fa-mobile-screen-button"></i></h1>
        <h5 class="h5" data-lang-en="Mobile Payments">Paiements Mobiles</h5>
        <p data-lang-en="Mobile Money, Orange Money, MTN MoMo integrated">
            Mobile Money, Orange Money, MTN MoMo <br> intégrés
        </p>
    </div>
    <div class="precreator">
        <h1><i class="fa-solid fa-mobile-screen-button"></i></h1>
        <h5 class="h5" data-lang-en="Mobile First">Mobile First</h5>
        <p data-lang-en="Optimized for Africa and mobile usage">Optimisé pour l'Afrique et l'usage mobile</p>
    </div>
    <div class="precreator">
        <h1><i class="fa-solid fa-gear"></i></h1>
        <h5 class="h5" data-lang-en="Setup in 10 minutes">Setup en 10min</h5>
        <p data-lang-en="Create your store in just a few minutes">Créez votre boutique en quelques minutes <br> seulement</p>
    </div>
    <div class="precreator">
        <h1><i class="fa-solid fa-chart-line"></i></h1>
        <h5 class="h5" data-lang-en="Advanced Analytics">Analytics Avancées</h5>
        <p data-lang-en="Get insights and track your store performance">
            Obtenez des insights et suivez les performances <br> de votre boutique
        </p>
    </div>
</div>
<section>
    <div class="ia" id="ia">
        <i  id="robot" class="fas fa-robot"></i>
    </div>
    <!-- zone de question -->
    <div id="div"></div>
</section>

<div class="steps" style="margin: 20px;">
    <div class="ma">
        <h4 style="text-align: center; font-size: 30px;" data-lang-en="How it works?">Comment ça marche?</h4>
        <p data-lang-en="3 simple steps to launch your business">3 étapes simples pour lancer votre business</p>
    </div>
    <div class="co-template">
        <h4 class="i1">1</h4>
        <h2 data-lang-en="Choose a template">Choisissez un template</h2>  
        <p data-lang-en="Restaurant, beauty salon, shop, services... More than 20 templates available.">
            Restaurant, salon de beauté, boutique, services...<br>Plus de 20 templates disponibles.
        </p>
    </div>
    <div class="co-template">
        <h4 class="i1">2</h4>
        <h2 data-lang-en="Customize">Personnalisez</h2>  
        <p data-lang-en="Add your images, colors, and texts in just a few clicks.">
            Ajoutez vos images, vos couleurs et vos textes<br>en quelques clics.
        </p>
    </div>
    <div class="co-template">
        <h4 class="i1">3</h4>
        <h2 data-lang-en="Publish">Publiez</h2>  
        <p data-lang-en="Put your shop online and instantly share your link or QR Code.">
            Mettez en ligne votre boutique et partagez<br>instantanément votre lien ou QR Code.
        </p>
    </div>
    <div class="co-template">
        <h4 class="i1">4</h4>
        <h2 data-lang-en="Collect Payments">Encaissez</h2>  
        <p data-lang-en="Receive your payments via Mobile Money, WhatsApp or online.">
            Recevez vos paiements via Mobile Money,<br>WhatsApp ou directement en ligne.
        </p>
    </div>
</div>
<section class="frequente">
    <div class="tous_contenu">
         <div class="title">
                <h2 class="question">Questions fréquentes</h2>
                <p class="reponse">Retrouvez les réponses aux questions les plus courantes</p>
            </div>
       <div class="faq">
                    <div class="faq-question">
                        <span>Creator Market est-il gratuit ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-reponse">
                        <p>Oui, pour débuter Creator Market est complètement gratuit et mis à la disposition de tous. Nous proposons également des formules premium avec des fonctionnalités avancées pour les entreprises en croissance.</p>
                    </div>
      </div>
        <div class="faq">
                    <div class="faq-question">
                        <span>Quels modes de paiement acceptez-vous ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-reponse">
                        <p>Nous acceptons tous les principaux modes de paiement utilisés en Afrique : Mobile Money (Orange Money, MTN MoMo), les cartes bancaires, et les virements. Nous travaillons également à intégrer de nouvelles solutions de paiement locales.</p>
                    </div>
                </div>
                 <div class="faq">
                    <div class="faq-question">
                        <span>Puis-je personnaliser ma boutique ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-reponse">
                        <p>Absolument ! Vous pouvez personnaliser votre boutique avec vos couleurs, images, textes et logo en quelques clics. Notre interface intuitive vous guide à chaque étape pour créer une boutique qui reflète votre identité de marque.</p>
                    </div>
                </div>
                 <div class="faq">
                    <div class="faq-question">
                        <span>Comment fonctionne l'intégration WhatsApp ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-reponse">
                        <p>L'intégration WhatsApp permet à vos clients de vous contacter directement depuis votre boutique en ligne. Ils peuvent poser des questions, passer des commandes ou demander des informations supplémentaires sans quitter votre site.</p>
                    </div>
                </div>
                
    </div>

<footer class="avant_footer">

</footer>

<footer class="pied-page">
<i class="fas fa-store" id="store"></i> 
<h2>Prêt à Vendre en Ligne ?</h2>
<p>Créez votre boutique gratuitement et commencez à vendre vos <br> produits via WhatsApp. Aucun site web nécessaire, aucun frais <br> cachés.</p>
<div class="conteneur_boutton"><button style="color: rgba(0, 0, 255, 0.76);" >
     <i class="fas fa-plus"></i>   Créer Ma Boutique</button>
     <button><i class="fas fa-info-circle" style="gap: 15px;"></i>     En Savoir Plus</button>
    </div>
    <div class="ensemble">
    <div class="pub">
        <i class="fas fa-bolt"></i>  
        <h3>Configuration Rapide</h3>
        <span>Créez votre boutique en moins de 5 <br> minutes</span>
    </div>
    <div class="pub">
        <i class="fas fa-shield-alt"></i>  
        <h3>Sécurité Garantie</h3>
        <span>Vos données et celles de vos clients sont <br> protégées</span>
    </div>
    <div class="pub">
<i class="fas fa-comments"></i> 
<h3>Vente via WhatsApp</h3>
<span>Communiquez directement avec vos <br> clients</span>
    </div>
    </div>
    <!-- <div class="conteneur-footer">
        <div class="contenu-footer">
            <div class="section-footer">
                <h3 class="titre-section" data-lang-en="Contact">Contact</h3>
                <div class="info-contact">
                    <i class="fas fa-map-marker-alt"></i>
                    <span data-lang-en="Nyalla Kambo, Douala">Nyalla Kambo, Douala</span>
                </div>
                <div class="info-contact">
                    <i class="fas fa-phone"></i>
                    <span data-lang-en="+237 657 30 06 44">+237 657 30 06 44</span>
                </div>
                <div class="info-contact">
                    <i class="fas fa-envelope"></i>
                    <span data-lang-en="ulrichbrayan492@gmail.com">ulrichbrayan492@gmail.com</span>
                </div>

                <div class="reseaux-sociaux">
                    <a href="#" class="bouton-reseau" id="facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="bouton-reseau" id="twitter">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="#" class="bouton-reseau" id="instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>

                <div class="formulaire-newsletter">
                    <input type="email" class="champ-email" placeholder="Votre email pour la newsletter" data-lang-en="Your email for the newsletter">
                    <button class="bouton-newsletter" onclick="inscrireNewsletter()" data-lang-en="Subscribe">S'inscrire</button>
                </div>
            </div>
        </div>
    </div>
    <div class="bas-footer">
        <div class="conteneur-footer">
            <p class="texte-copyright" data-lang-en="© 2025 Creator Market. All rights reserved.">
                © 2025 Creator Market. Tous droits réservés.
            </p>
            <div class="liens-legaux">
                <a href="#" class="lien-legal" id="confidentialite" data-lang-en="Privacy">Confidentialité</a>
                <a href="#" class="lien-legal" id="conditions" data-lang-en="Terms of Use">Conditions d'utilisation</a>
            </div>
        </div>
    </div> -->
</footer>
</div>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="src/js/acceuil.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>