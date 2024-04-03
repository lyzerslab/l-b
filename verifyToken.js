const jwt = require("jsonwebtoken");

// Replace these values with your actual secret key and token
const secretKey = "54GsRS45";
const token =
  "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxNCwidXNlcm5hbWUiOiJ6a3JhbmEifQ.iwCZ2MPgAvGNz61O83MSpYsKzCWGT9j1MCLxroJscdY";

try {
  const decoded = jwt.verify(token, secretKey);
  console.log("Decoded Token:", decoded);
} catch (error) {
  console.error("Error decoding token:", error.message);
}
