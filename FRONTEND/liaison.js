// window.onload = function() {
//   const button = document.getElementById('bouton-soumettre');

//   // Gestion logo
//   const fichierLogo = document.getElementById('fichier-logo');
//   if (fichierLogo) {
//     fichierLogo.addEventListener('change', function(e) {
//       const file = e.target.files[0];
//       const placeholder = document.getElementById('placeholder-logo');
//       const apercu = document.getElementById('apercu-logo');
      
//       if (file && file.type.startsWith('image/')) {
//         const reader = new FileReader();
//         reader.onload = function(e) {
//           apercu.src = e.target.result;
//           apercu.classList.remove('image-cachee');
//           apercu.classList.remove('hidden');
//           placeholder.classList.add('hidden');
//         };
//         reader.readAsDataURL(file);
//       }
//     });
//   }

//   // Fonction pour créer la boutique
//      const nom = document.getElementById('nom-salon').value.trim();
//   function creerBoutique() {
//      const apercu =document.getElementById('fichier-logo').value.trim();
//     const nom = document.getElementById('nom-salon').value.trim();
//     const service = document.getElementById('services-salon').value.trim();
//     const description = document.getElementById('descriptions-salon').value.trim();
//     const whatsapp = document.getElementById('whatsapp-salon').value.trim();
//     const localisation = document.getElementById('adresse-salon').value.trim();
//     const logo = document.getElementById('apercu-logo').src;
//     if (!nom || !service || !description || !whatsapp || !localisation||!logo) {
//       alert("Veuillez remplir tous les champs obligatoires et ajouter une image");
//       return false;
//     }
//     if (!whatsapp.match(/^\+?[0-9\s-]{9,}$/)) {
//       alert(" Veuillez entrer un numéro WhatsApp valide");
//       return false;
//     }

//     // Sauvegarde des données de la boutique
//     const boutique = {
//       nom: nom,
//       service: service,
//       description: description,
//       whatsapp: whatsapp,
//       localisation: localisation,
//       logo: logo || '',
//       dateCreation: new Date().toISOString()
//     };
//     try {
//       sessionStorage.setItem('boutique', JSON.stringify(boutique));
//             window.location.href = 'dashbord.html';
//       return true;
//     } catch (error) {
//       console.error('Erreur lors de la sauvegarde:', error);
//       alert("Boutique créée ! (Redirection vers le dashboard...)");
//             setTimeout(() => {
//         window.location.href = 'dashbord.html';
//       }, 1000);
//     }
//   }

//   if (button) {
//     button.addEventListener('click', function(e) {
//       e.preventDefault();
//       creerBoutique();
//     });
//   }

// const nomElement = document.getElementById('nom');
// const logoElement = document.getElementById('logo');
// const date_creation = document.getElementById('date_creation');

// try {
//   const boutiqueData = sessionStorage.getItem('boutique');
//   if (!boutiqueData) throw "Aucune boutique trouvée";
//   const boutique = JSON.parse(boutiqueData);
//   if (nomElement) nomElement.textContent = boutique.nom;

//   if (date_creation && boutique.dateCreation) {
//     const date = new Date(boutique.dateCreation);
//     date_creation.textContent = date.toLocaleString();
//   }

//   if (logoElement && boutique.logo) logoElement.src = boutique.logo;

// } catch (error) {
//   console.error("Erreur lors de la récupération des données :", error);
// }
//  };
// //  //gestion des articles
// //      soumettre=document.getElementById('bouton-soumettre');
// //       businesscard=document.getElementById('businesses-grid');
// //    function ajouter_article() {
// //     const nom = document.getElementById('nom-article').value;
// //     const localisation = document.getElementById('localisation').value;
// //     const description = document.getElementById('descriptions-article').value;
// //     const prix = document.getElementById('prix-article').value;
// //     const numero = document.getElementById('whatsapp-salon').value;
// //     if (!nom || !localisation || !description || !prix || !numero) {
// //         alert("Veuillez remplir les champs manquants");
// //         return false;
// //     } else {
// //         const div = document.createElement('div');
// //         div.innerHTML = `
// //             <div class="business-card">
// //                 <div class="business-image">
// //                     <div class="featured-badge">⭐ Vedette</div>
// //                     <i class="fas fa-utensils"></i>
// //                 </div>
// //                 <div class="business-info">
// //                     <div class="business-name">${nom}<button class="coeur">❤</button></div>
// //                     <div class="business-category">${description}</div>
// //                     <div class="business-prix" data-prix="${prix}" style="display:none;">${prix}</div>
// //                     <div class="business-location"><i class="fas fa-map-marker-alt"></i>${localisation}</div>
// //                     <div class="business-rating">
// //                         <span class="stars">⭐⭐⭐⭐⭐</span>
// //                         <span data-nb="78" data-rating="4.8" class="avis">4.8 (78 avis)</span>
// //                     </div>
// //                     <div class="business-actions">
// //                         <button class="action-btn btn-primary"> 
// //                             <i class="fas fa-shopping-cart"></i> Commander
// //                         </button>
// //                         <button class="action-btn btn-secondary" onclick="window.location.href='site.html'"> 
// //                             <i class="fas fa-eye"></i> Voir la Boutique
// //                         </button>
// //                         <button class="partage" style="text-align: center;">
// //                             <i class="fas fa-share-alt"></i>
// //                         </button>
// //                     </div>
// //                 </div>
// //             </div>
// //         `;
        
// //         // Corrigé : appendChild au lieu de appenChild
// //         document.getElementById('businesses-grid').appendChild(div);
// //         alert("Produits publier avec succes!!!");
// //         return true;
// //     }
// // }

// // // Événement pour le formulaire
// // document.getElementById('formulaire-inscription').addEventListener('submit', function(e) {
// //     e.preventDefault();
// //     ajouter_article();
// // });
