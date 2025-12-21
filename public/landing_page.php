<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FKPARK 2024</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<nav class="navbar navbar-light bg-light">
  <span class="navbar-brand mb-0 h1">FKPARK 2024</span>
  <button class="btn btn-primary" onclick="goLogin()">Login</button>
</nav>

<div class="container text-center mt-5">
  <h1>Welcome to FKPARK</h1>
  <p>Smart parking system for FK students, staff and administrators.</p>
</div>

<script>
function goLogin(){
  window.location.href = "login_page.php";
}
</script>
</body>
</html>
