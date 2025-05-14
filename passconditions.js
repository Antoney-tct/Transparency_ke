document.querySelector('form').addEventListener('submit', function(e) {
    const password = this.querySelector('[name="password"]').value;
    const confirm = this.querySelector('[name="confirm_password"]').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters');
        return false;
    }
    
    // For government form only
    if (this.action.includes('government')) {
        const email = this.querySelector('[name="email"]').value;
        if (!email.includes('gov')) {
            e.preventDefault();
            alert('Government email must contain "gov"');
            return false;
        }
    }
});