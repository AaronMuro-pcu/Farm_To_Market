<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FarmLinked | Farm-to-Market Linkage</title>
  <link rel="icon" href="../assets/images/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css?v=4">
</head>
<body>

  <!-- NAVBAR -->
  <header class="navbar">
    <a href="#home" class="logo" aria-label="FarmLinked home">
      <img src="../assets/images/logo.png" alt="" class="logo-img">
      <span class="logo-text">FarmLinked</span>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">&#9776;</button>

    <nav class="nav-links" id="navLinks">
      <a href="#home">Home</a>
      <a href="#how-it-works">How it works</a>
      <a href="#contact">Contact us</a>
    </nav>

    <a href="../auth/login.php" class="btn btn-primary btn-login">Login</a>
  </header>

  <main>
    <!-- HERO -->
    <section class="hero" id="home">
      <div class="hero-text">
        <h1>Fresh from the farm, straight to your table</h1>
        <p>
          We connect local farmers directly with buyers, so growers earn a
          fair price and families get produce picked at its best.
        </p>
        <div class="hero-actions">  
          <a href="../auth/login.php" class="btn btn-primary" id="startTrial">Let's Start</a>
          <a href="#how-it-works" class="btn btn-ghost">See how it works</a>
        </div>
      </div>
    </section>

    <!-- TRUST STRIP -->
    <ul class="strip">
      <li>No middlemen</li>
      <li>Farmers set their own price</li>
      <li>Pickup or delivery</li>
      <li>Ratings you can trust</li>
    </ul>

    <!-- VALUE PROPOSITION -->
    <section class="value" id="features">
      <span class="eyebrow">Why FarmLinked</span>
      <h2>One marketplace, fair for both sides</h2>
      <p>
        Skip the long chain of middlemen. Farmers set their own prices and
        buyers know exactly where their food comes from.
      </p>

      <div class="cards">
        <article class="card">
          <div class="card-media">
            <img src="../assets/images/plowing.jpg" alt="A farmer plowing a rice field with a water buffalo" loading="lazy">
            <span class="card-tag">For farmers</span>
          </div>
          <div class="card-body">
            <h3>Sell directly, set your price</h3>
            <p>List your harvest in minutes, set your price, and reach buyers beyond your barangay.</p>
            <a href="#how-it-works" class="learn-more">Learn more</a>
          </div>
        </article>
        <article class="card">
          <div class="card-media">
            <img src="../assets/images/pumpkin-seeds.jpg" alt="Hands holding pumpkin seeds" loading="lazy">
            <span class="card-tag">For buyers</span>
          </div>
          <div class="card-body">
            <h3>Know where your food comes from</h3>
            <p>Order fresh vegetables, fruit and staples straight from growers you can trust.</p>
            <a href="#how-it-works" class="learn-more">Learn more</a>
          </div>
        </article>
        <article class="card">
          <div class="card-media">
            <img src="../assets/images/rice-workers.jpg" alt="Farmers planting rice in flooded paddies" loading="lazy">
            <span class="card-tag">Reliable delivery</span>
          </div>
          <div class="card-body">
            <h3>Fresh from the field</h3>
            <p>Schedule pickups or deliveries so produce reaches the market while it is still fresh.</p>
            <a href="#how-it-works" class="learn-more">Learn more</a>
          </div>
        </article>
      </div>
    </section>

    <!-- STORY -->
    <section class="story" id="stories">
      <div class="story-media">
        <img class="arch" src="../assets/images/seeds.jpg" alt="Hands holding a pile of dark seeds" loading="lazy">
        <img class="dot" src="../assets/images/pumpkin-seeds.jpg" alt="Hands holding pumpkin seeds" loading="lazy">
      </div>
      <div class="story-text">
        <span class="eyebrow">Our story</span>
        <h2>Grown by real hands</h2>
        <p>Every order starts with people who prepare the paddy, save the seed and bring in the harvest. This platform puts more of the sale back in their hands.</p>
        <ul class="ticks">
          <li><strong>Know the source.</strong> See who grew your food and where.</li>
          <li><strong>Fair for growers.</strong> Fewer hands between the field and the buyer.</li>
          <li><strong>Fresher produce.</strong> Shorter trips from farm to table.</li>
        </ul>
        <a href="../auth/login.php" class="btn btn-primary">Join the community</a>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how" id="how-it-works">
      <div class="how-intro">
        <span class="eyebrow">Simple steps</span>
        <h2>How it works</h2>
        <p>From harvest to doorstep in five simple steps.</p>
      </div>

      <ol class="steps">
        <li class="step">
          <span class="step-num" aria-hidden="true">1</span>
          <div>
            <h3>Create your account</h3>
            <p>Sign up as a farmer or a buyer. It only takes a minute.</p>
          </div>
        </li>
        <li class="step">
          <span class="step-num" aria-hidden="true">2</span>
          <div>
            <h3>Farmers list their harvest</h3>
            <p>Add what you grow, how much is available and your price.</p>
          </div>
        </li>
        <li class="step">
          <span class="step-num" aria-hidden="true">3</span>
          <div>
            <h3>Buyers browse and order</h3>
            <p>Search by crop or location and place an order directly.</p>
          </div>
        </li>
        <li class="step">
          <span class="step-num" aria-hidden="true">4</span>
          <div>
            <h3>Confirm and arrange delivery</h3>
            <p>Both sides agree on pickup or delivery time and place.</p>
          </div>
        </li>
        <li class="step">
          <span class="step-num" aria-hidden="true">5</span>
          <div>
            <h3>Receive fresh produce</h3>
            <p>Get your order, then leave a rating to help the community.</p>
          </div>
        </li>
      </ol>
    </section>
  </main>

  <!-- CALL TO ACTION -->
  <section class="cta">
    <div>
      <h2>Plant the first seed</h2>
      <p>Create a free account as a farmer or a buyer and start trading directly.</p>
      <a href="../auth/login.php" class="btn btn-sun">Create your account</a>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer" id="contact">
    <div class="footer-left">
      <div class="footer-logo">
        <img src="../assets/images/logo.png" alt="" class="logo-img">
        <span>FarmLinked</span>
      </div>
      <h3>Ready to get started?</h3>
      <p>
        Join farmers and buyers who are building a fairer, fresher food
        supply together. Create your free account today.
      </p>
    </div>

    <div class="footer-right">
      <h3>Contact Us</h3>
      <div class="footer-cols">
        <ul>
          <li class="col-title">Product</li>
          <li><a href="#features">Features</a></li>
          <li><a href="#how-it-works">How it works</a></li>
          <li><a href="../auth/signup.php">Sign up</a></li>
          <li><a href="../auth/login.php">Login</a></li>
        </ul>
        <ul>
          <li class="col-title">Company</li>
          <li><a href="#home">About us</a></li>
          <li><a href="#contact">Support</a></li>
          <li><a href="#contact">Privacy</a></li>
          <li><a href="#contact">Terms</a></li>
        </ul>
      </div>
    </div>
  </footer>

  <script src="../assets/js/script.js"></script>
</body>
</html>