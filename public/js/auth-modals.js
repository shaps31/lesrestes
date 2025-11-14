// Switch entre modales connexion/inscription
function switchToRegister(e) {
    e.preventDefault();
    const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
    loginModal.hide();
    const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    registerModal.show();
}

function switchToLogin(e) {
    e.preventDefault();
    const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
    registerModal.hide();
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    loginModal.show();
}