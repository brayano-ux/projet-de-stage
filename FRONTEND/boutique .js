document.getElementById('crer_boutique').addEventListener('click', function(e) {
  e.preventDefault();
  creer_boutique();
});

function creer_boutique() {
  const nom = document.getElementById('nom-salon').value.trim();
  const service = document.getElementById('services-salon').value.trim();
  const description = document.getElementById('descriptions-salon').value.trim();
  const whatsapp = document.getElementById('whatsapp-salon').value.trim();
  const localisation = document.getElementById('adresse-salon').value.trim();
  const image = document.getElementById('apercu-logo').src;

  if (!nom || !service || !description || !whatsapp || !localisation || !image) {
    alert("Veuillez remplir tous les champs et choisir un logo");
    return;
  }

  // Sauvegarde dans localStorage
  localStorage.setItem('nom_footer',nom);
  localStorage.setItem('nom_personne', nom);
  localStorage.setItem('service', service);
  localStorage.setItem('description', description);
  localStorage.setItem('whatsapp', whatsapp);
  localStorage.setItem('localisation', localisation);
  localStorage.setItem('logo', image);
  localStorage.setItem('date_creation', new Date().toISOString());

  // Redirection vers le dashboard
  window.location.href = "dashbord.html";
}

// Aperçu du logo
const placeholder = document.getElementById('placeholder-logo');
const imageInput = document.getElementById('fichier-logo');
const apercu = document.getElementById('apercu-logo');

imageInput.addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file && file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = function(e) {
      apercu.src = e.target.result;
      apercu.classList.remove('hidden');
      placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
  }
});
//fonction soumettre article
function soumettre_article() {
    const nom = document.getElementById("nom-article").value.trim();
    const prix = document.getElementById("prix-article").value.trim();
    const localisation = document.getElementById("localisation").value.trim();
    const description = document.getElementById("description-article").value.trim();
    const whatsapp = document.getElementById("whatsapp-article").value.trim();
    const fichier = document.getElementById("fichier-logo").files[0];

    if (!nom || !prix || !localisation || !description || !whatsapp) {
      alert("Veuillez remplir tous les champs obligatoires");
      return;
    }
    if (!fichier) {
      alert("Veuillez importer une image");
      return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      const image = e.target.result;
      localStorage.setItem("nom", nom);
      localStorage.setItem("prix", prix);
      localStorage.setItem("localisation", localisation);
      localStorage.setItem("description", description);
      localStorage.setItem("whatsapp", whatsapp);
      localStorage.setItem("image", image);
      window.location.href = "index.html";
    };
    reader.readAsDataURL(fichier);
  }

  document.getElementById("bouton-soumettre").addEventListener("click", function (e) {
    e.preventDefault();
    soumettre_article();
  });
