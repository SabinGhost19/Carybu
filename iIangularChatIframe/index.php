{source}<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tech & Lifestyle Blog</title>
<style>
* {
margin: 0;
padding: 0;
box-sizing: border-box;
}

body {
font-family: 'Arial', sans-serif;
line-height: 1.6;
background-color: #f4f4f4;
color: #333;
}

.container {
width: 90%;
max-width: 1200px;
margin: 0 auto;
padding: 20px;
}

.featured-posts {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
gap: 30px;
margin-bottom: 40px;
}

.post-card {
background-color: #ffffff;
border-radius: 8px;
box-shadow: 0 4px 6px rgba(0,0,0,0.1);
overflow: hidden;
transition: transform 0.3s ease;
}

.post-card:hover {
transform: translateY(-5px);
}

.post-card img {
width: 100%;
height: 250px;
object-fit: cover;
}

.post-content {
padding: 20px;
}

.post-content h2 {
margin-bottom: 10px;
color: #2c3e50;
font-size: 1.5em;
}

.post-content p {
color: #7f8c8d;
margin-bottom: 15px;
}

.post-meta {
display: flex;
justify-content: space-between;
align-items: center;
font-size: 0.9em;
color: #95a5a6;
}

.read-more {
text-decoration: none;
color: #3498db;
font-weight: bold;
}

footer {
background-color: #2c3e50;
color: #ffffff;
text-align: center;
padding: 20px 0;
margin-top: 30px;
}

@media screen and (max-width: 768px) {
.featured-posts {
grid-template-columns: 1fr;
}
}
</style>
</head>
<body>
<div class="container">
<section class="featured-posts">
<article class="post-card">
<img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1000&q=80" alt="Artificial Intelligence">
<div class="post-content">
<h2>The Rise of Artificial Intelligence</h2>
<p>Exploring the latest breakthroughs in AI technology and their potential impact on various industries.</p>
<div class="post-meta">
<span>April 15, 2024</span>
<a href="#" class="read-more">Read More</a>
</div>
</div>
</article>

<article class="post-card">
<img src="https://images.unsplash.com/photo-1484417894907-623942c8ee29?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1000&q=80" alt="Sustainable Living">
<div class="post-content">
<h2>Sustainable Living: A Modern Approach</h2>
<p>Practical tips and innovative strategies for reducing your carbon footprint and living more sustainably.</p>
<div class="post-meta">
<span>April 10, 2024</span>
<a href="#" class="read-more">Read More</a>
</div>
</div>
</article>

<article class="post-card">
<img src="https://images.unsplash.com/photo-1516321497487-e288fb19713f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1000&q=80" alt="Remote Work">
<div class="post-content">
<h2>The Future of Remote Work</h2>
<p>Analyzing the changing landscape of work and how remote collaboration is reshaping modern businesses.</p>
<div class="post-meta">
<span>April 5, 2024</span>
<a href="#" class="read-more">Read More</a>
</div>
</div>
</article>

<article class="post-card">
<img src="https://images.unsplash.com/photo-1605379399642-870c3e2e5d0a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1000&q=80" alt="Mental Health">
<div class="post-content">
<h2>Mental Health in the Digital Age</h2>
<p>Exploring the psychological impacts of technology and strategies for maintaining mental well-being.</p>
<div class="post-meta">
<span>March 28, 2024</span>
<a href="#" class="read-more">Read More</a>
</div>
</div>
</article>

<article class="post-card">
<img src="https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1000&q=80" alt="Innovation">
<div class="post-content">
<h2>Innovating for Tomorrow</h2>
<p>A deep dive into cutting-edge technologies and innovative solutions solving global challenges.</p>
<div class="post-meta">
<span>March 20, 2024</span>
<a href="#" class="read-more">Read More</a>
</div>
</div>
</article>
</section>
</div>


</body>
</html>{/source}{source}<?php
// Database connection parameters
$host = 'db'; // Docker container service name
$username = 'joomla_user';
$password = 'joomla_pass';
$database = 'joomla_db';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}

// Query to fetch users
$sql = "SELECT * FROM users";
echo $sql;
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Utilizatori</title>
<style>
body {
font-family: Arial, sans-serif;
margin: 0 auto;
padding: 20px;
background-color: #f4f4f4;
}
.user-card {
background-color: white;
border-radius: 8px;
padding: 15px;
margin-bottom: 15px;
box-shadow: 0 2px 5px rgba(0,0,0,0.1);
transition: transform 0.3s ease;
}
.user-card:hover {
transform: scale(1.02);
}
.user-name {
font-size: 1.2em;
font-weight: bold;
color: #333;
margin-bottom: 10px;
}
.user-details {
color: #666;
}
</style>
</head>
<body>
<h1>Lista Utilizatori</h1>
<?php
if ($result->num_rows > 0) {
// Output data of each row
while($row = $result->fetch_assoc()) {
echo "<div class='user-card'>";
echo "<div class='user-name'>" . htmlspecialchars($row["name"]) . "</div>";
echo "<div class='user-details'>";
echo "<p>Descriere: " . htmlspecialchars($row["description"]) . "</p>";
echo "<p>Vârstă: " . htmlspecialchars($row["age"]) . " ani</p>";
echo "<p>Facultate: " . htmlspecialchars($row["faculty"]) . "</p>";
echo "</div>";
echo "</div>";
}
} else {
echo "<p>Niciun utilizator găsit.</p>";
}

// Close connection
$conn->close();
?>
</body>
</html>{/source}