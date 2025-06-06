function validateLogin() {
  const emailField = document.getElementById("email");
  const passwordField = document.getElementById("password");
  const email = emailField.value.trim();
  const password = passwordField.value.trim();

  const emailError = document.getElementById("emailError");
  const passwordError = document.getElementById("passwordError");

  // Reset old styles
  emailError.textContent = "";
  passwordError.textContent = "";
  emailField.classList.remove("input-error");
  passwordField.classList.remove("input-error");

  let isValid = true;

  if (email === "") {
    emailError.textContent = "Email is required.";
    emailField.classList.add("input-error");
    isValid = false;
  } else {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
      emailError.textContent = "Enter valid email or mobile number.";
      emailField.classList.add("input-error");
      isValid = false;
    }
  }

  if (password === "") {
    passwordError.textContent = "Password is required.";
    passwordField.classList.add("input-error");
    isValid = false;
  } else if (password.length < 6) {
    passwordError.textContent = "Password must be at least 6 characters.";
    passwordField.classList.add("input-error");
    isValid = false;
  }

  return isValid;
}
