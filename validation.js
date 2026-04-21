

/**
 * Affiche un message d'erreur sous un champ.
 param {HTMLElement} input  
 param {string}      msg    
 */
function showError(input, msg) {
  clearError(input);                        // evite les doublons

  input.classList.add('input-error');       // bordure rouge sur le champ

  const span = document.createElement('span');
  span.className = 'error-msg';
  span.textContent = msg;

  // On insère le message juste après le champ (ou après le <small> s'il existe)
  const sibling = input.nextElementSibling;
  if (sibling && sibling.tagName === 'SMALL') {
    sibling.after(span);
  } else {
    input.after(span);
  }
}

//Supprime le message d'erreur d'un champ.
function clearError(input) {
  input.classList.remove('input-error');
  input.classList.remove('input-ok');

  // Cherche le span d'erreur SUIVANT (peut être après un <small>)
  let next = input.nextElementSibling;
  while (next) {
    if (next.classList && next.classList.contains('error-msg')) {
      next.remove();
      break;
    }
    next = next.nextElementSibling;
  }
}


//Supprime le message d'erreur d'un champ.
function markOk(input) {
  clearError(input);
  input.classList.add('input-ok');
}


//Retourne true si la chaîne est vide (après trim).
function isEmpty(value) {
  return value.trim() === '';
}

//RÈGLES DE VALIDATION

const REGEX = {
  // Email standard
  email: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,

  // Téléphone : +XXX XXX XXX XXXX ou local 0XXXXXXXXX (algérien / international)
  phone: /^(\+?\d[\d\s\-]{7,15}\d)$/,

  // Numéro d'Identification National : 18 chiffres (comme la carte nationale)
  nin: /^\d{18}$/,

  // Mot de passe : min 8 caractères, au moins 1 lettre et 1 chiffre
  password: /^(?=.*[A-Za-z])(?=.*\d).{8,}$/,

  // Nom et Prénom : lettres, espaces, tirets, apostrophes (accents inclus)
  name: /^[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,50}$/,

  // Adresse : min 10 caractères
  address: /^.{10,}$/,
};

//FONCTIONS DE VALIDATION PAR TYPE

/** Valide un champ obligatoire non vide. */
function validateRequired(input, label) {
  if (isEmpty(input.value)) {
    showError(input, `${label} est obligatoire.`);
    return false;
  }
  markOk(input);
  return true;
}

/** Valide le format d'un email. */
function validateEmail(input) {
  if (!validateRequired(input, 'L\'adresse email')) return false;
  if (!REGEX.email.test(input.value.trim())) {
    showError(input, 'Format d\'email invalide. Exemple : nom@domaine.com');
    return false;
  }
  markOk(input);
  return true;
}

/** Valide le format d'un numéro de téléphone. */
function validatePhone(input) {
  if (!validateRequired(input, 'Le numéro de téléphone')) return false;
  const cleaned = input.value.replace(/\s/g, '');
  if (!REGEX.phone.test(input.value.trim())) {
    showError(input, 'Numéro invalide. Exemple : +213 555 123 456 ou 0555123456');
    return false;
  }
  markOk(input);
  return true;
}

/** Valide le NIN (18 chiffres). */
function validateNIN(input) {
  if (!validateRequired(input, 'Le NIN')) return false;
  if (!REGEX.nin.test(input.value.trim())) {
    showError(input, 'Le NIN doit contenir exactement 18 chiffres.');
    return false;
  }
  markOk(input);
  return true;
}

/** Valide un champ nom / prénom. */
function validateName(input, label) {
  if (!validateRequired(input, label)) return false;
  if (!REGEX.name.test(input.value.trim())) {
    showError(input, `${label} invalide. Utilisez uniquement des lettres, espaces ou tirets.`);
    return false;
  }
  markOk(input);
  return true;
}

/** Valide la date de naissance : l'utilisateur doit avoir ≥ 18 ans. */
function validateDOB(input) {
  if (!validateRequired(input, 'La date de naissance')) return false;

  const dob = new Date(input.value);
  if (isNaN(dob.getTime())) {
    showError(input, 'Date invalide.');
    return false;
  }

  const today = new Date();
  let age = today.getFullYear() - dob.getFullYear();
  const monthDiff = today.getMonth() - dob.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
    age--;
  }

  if (age < 18) {
    showError(input, `Vous devez avoir au moins 18 ans. Âge actuel : ${age} an(s).`);
    return false;
  }
  if (age > 120) {
    showError(input, 'Date de naissance non valide.');
    return false;
  }

  markOk(input);
  return true;
}

/** Valide un mot de passe (min 8 chars, lettres + chiffres). */
function validatePassword(input) {
  if (!validateRequired(input, 'Le mot de passe')) return false;
  if (!REGEX.password.test(input.value)) {
    showError(input, 'Le mot de passe doit contenir au moins 8 caractères, une lettre et un chiffre.');
    return false;
  }
  markOk(input);
  return true;
}

/** Valide la confirmation du mot de passe. */
function validatePasswordConfirm(input, passwordInput) {
  if (!validateRequired(input, 'La confirmation du mot de passe')) return false;
  if (input.value !== passwordInput.value) {
    showError(input, 'Les mots de passe ne correspondent pas.');
    return false;
  }
  markOk(input);
  return true;
}

/** Valide une adresse (min 10 caractères). */
function validateAddress(input) {
  if (!validateRequired(input, 'L\'adresse')) return false;
  if (!REGEX.address.test(input.value.trim())) {
    showError(input, 'L\'adresse doit contenir au moins 10 caractères.');
    return false;
  }
  markOk(input);
  return true;
}

/** Valide une case à cocher obligatoire (ex : CGU). */
function validateCheckbox(input, label) {
  if (!input.checked) {
    showError(input, `Vous devez accepter ${label}.`);
    return false;
  }
  clearError(input);
  return true;
}

//VALIDATION EN TEMPS RÉEL (événement blur) Attache la validation à un champ dès qu'il perd le focus — retour immédiat à l'utilisateur.

/**
 * Attache un validateur "on-blur" à un champ.
 * @param {HTMLElement} input      - Le champ
 * @param {Function}    validator  - La fonction de validation à appeler
 * @param {...*}        args       - Arguments supplémentaires passés au validateur
 */
function attachBlurValidation(input, validator, ...args) {
  if (!input) return;
  input.addEventListener('blur', () => validator(input, ...args));
  // Nettoyage de l'erreur dès que l'utilisateur commence à corriger
  input.addEventListener('input', () => {
    clearError(input);
  });
}

//INJECTION DES STYLES D'ERREUR (évite un fichier CSS supplémentaire)

(function injectValidationStyles() {
  const style = document.createElement('style');
  style.textContent = `
  
    .input-error {
      border-color: #c0392b !important;
      box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.15) !important;
      background-color: #fff8f8 !important;
    }
    
    .input-ok {
      border-color: #2d6a4f !important;
      box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.12) !important;
      background-color: #f4fdf7 !important;
    }
  
    .error-msg {
      display: block;
      margin-top: 5px;
      color: #c0392b;
      font-size: 12.5px;
      font-weight: 500;
      letter-spacing: 0.1px;
      animation: errorAppear 0.25s ease;
    }
    @keyframes errorAppear {
      from { opacity: 0; transform: translateY(-4px); }
      to   { opacity: 1; transform: translateY(0); }
    }
  
    .pwd-strength {
      margin-top: 6px;
      height: 4px;
      border-radius: 2px;
      transition: all 0.3s ease;
      background: #e0e0e0;
    }
    .pwd-strength.weak   { background: #e74c3c; width: 30%; }
    .pwd-strength.medium { background: #f39c12; width: 65%; }
    .pwd-strength.strong { background: #2d6a4f; width: 100%; }
    .pwd-strength-label {
      font-size: 11px;
      margin-top: 3px;
      color: var(--text-muted);
    }

    .char-counter {
      display: block;
      text-align: right;
      font-size: 11px;
      color: var(--text-muted);
      margin-top: 4px;
    }

    .form-alert {
      padding: 14px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      font-weight: 500;
      display: none;
      animation: errorAppear 0.3s ease;
    }
    .form-alert.success {
      background: #d4edde;
      color: #1a4731;
      border: 1px solid #2d6a4f;
      display: block;
    }
    .form-alert.error {
      background: #fde8e8;
      color: #7b1c1c;
      border: 1px solid #c0392b;
      display: block;
    }
  `;
  document.head.appendChild(style);
})();


function attachPasswordStrength(input) {
  if (!input) return;

  //tcriyi la barre
  const bar = document.createElement('div');
  bar.className = 'pwd-strength';
  const label = document.createElement('span');
  label.className = 'pwd-strength-label';

  input.after(bar);
  bar.after(label);

  input.addEventListener('input', () => {
    const v = input.value;
    let score = 0;
    if (v.length >= 8)              score++;
    if (/[A-Z]/.test(v))            score++;
    if (/[0-9]/.test(v))            score++;
    if (/[^A-Za-z0-9]/.test(v))     score++;

    bar.className = 'pwd-strength';
    if (v.length === 0) { label.textContent = ''; return; }

    if (score <= 1) {
      bar.classList.add('weak');
      label.textContent = 'Force : faible';
      label.style.color = '#e74c3c';
    } else if (score <= 2) {
      bar.classList.add('medium');
      label.textContent = 'Force : moyenne';
      label.style.color = '#f39c12';
    } else {
      bar.classList.add('strong');
      label.textContent = 'Force : forte ✓';
      label.style.color = '#2d6a4f';
    }
  });
}

//UTILITAIRE : Afficher une alerte globale

/**
 * Affiche (ou crée) une alerte en haut du formulaire.
 * @param {HTMLElement} form    - Le formulaire
 * @param {string}      msg     - Le message
 * @param {'success'|'error'} type
 */
function showFormAlert(form, msg, type) {
  let alert = form.querySelector('.form-alert');
  if (!alert) {
    alert = document.createElement('div');
    alert.className = 'form-alert';
    form.prepend(alert);
  }
  alert.className = `form-alert ${type}`;
  alert.textContent = msg;
  alert.scrollIntoView({ behavior: 'smooth', block: 'center' });

  // Auto-disparition après 5 s pour les succès
  if (type === 'success') {
    setTimeout(() => { alert.style.display = 'none'; }, 5000);
  }
}
