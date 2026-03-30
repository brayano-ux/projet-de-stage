function sendMail() {
    var params = {
        from_name : document.getElementById("fullname").value,
        email_id : document.getElementById("email_id").value,
        message : document.getElementById("message").value
    }
    emailjs.send("service_b2bjjac", "template_zg4u3bk", params).then(function(res)
    alert("Success! " + res.status));
}