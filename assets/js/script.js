function validateForm() {
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();

  if (email === "" || password === "") {
    alert("All fields are required!");
    return false;
  }

  // Basic email format validation
  const pattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
  if (!pattern.test(email)) {
    alert("Please enter a valid email address.");
    return false;
  }

  // Password length validation (optional)
  if (password.length < 6) {
    alert("Password must be at least 6 characters.");
    return false;
  }

  return true;
}
