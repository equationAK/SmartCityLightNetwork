
    function validateForm() {
        let email = document.forms["regForm"]["email"].value;
        let password = document.forms["regForm"]["password"].value;
        let confirmPassword = document.forms["regForm"]["confirm_password"].value;
        let username = document.forms["regForm"]["username"].value;

        if (!email.includes("@")) {
            alert("Πληκτρολογείστε ένα έγκυρο email.");
            return false;
        }

        if (password.length < 8) {
            alert("Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.");
            return false;
        }

        if (!/\d/.test(password)) {
            alert("Ο κωδικός πρέπει να περιλαμβάνει τουλάχιστον έναν αριθμό.");
            return false;
        }

        if (!/[A-Z]/.test(password)) {
            alert("Ο κωδικός πρέπει να περιλαμβάνει τουλάχιστον ένα κεφαλαίο γράμμα.");
            return false;
        }

        if (password !== confirmPassword) {
            alert("Οι κωδικοί δεν ταιριάζουν.");
            return false;
        }
        return true;
    }