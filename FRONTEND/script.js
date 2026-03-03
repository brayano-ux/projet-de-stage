function sendMail() {
    var params = {
        from_name : document.getElementById("fullname").value,
        email_id : document.getElementById("email_id").value,
        message : document.getElementById("message").value
    }
    emailjs.send("service_b2bjjac", "template_zg4u3bk", params).then(function(res)
    alert("Success! " + res.status));
}

// Add login form validation
document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('remember-me').checked;

    // Validation des champs
    let isValid = true;

    if (username.trim() === '') {
        document.getElementById('username-error').textContent = 'Le nom d\'utilisateur est requis.';
        document.getElementById('username-error').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('username-error').style.display = 'none';
    }

    if (password.trim() === '') {
        document.getElementById('password-error').textContent = 'Le mot de passe est requis.';
        document.getElementById('password-error').style.display = 'block';
        isValid = false;
    } else {
        document.getElementById('password-error').style.display = 'none';
    }

    if (!isValid) {
        return;
    }

    // Exemple de validation simple
    if (username === 'admin' && password === 'password') {
        if (rememberMe) {
            localStorage.setItem('username', username);
            localStorage.setItem('password', password);
        } else {
            localStorage.removeItem('username');
            localStorage.removeItem('password');
        }
        alert('Connexion réussie!');
        // Rediriger vers une autre page ou effectuer une autre action
    } else {
        alert('Nom d\'utilisateur ou mot de passe incorrect.');
    }
});

// Remplir les champs si les informations sont stockées
window.addEventListener('load', function() {
    const savedUsername = localStorage.getItem('username');
    const savedPassword = localStorage.getItem('password');

    if (savedUsername && savedPassword) {
        document.getElementById('username').value = savedUsername;
        document.getElementById('password').value = savedPassword;
        document.getElementById('remember-me').checked = true;
    }
});

