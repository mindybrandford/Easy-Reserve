<?php
session_start();
// Function to check if the user is logged in
function isLoggedIn()
{
    return isset($_SESSION['username']);
}

// Function to get the logged-in user's name
function getUserName()
{
    return isset($_SESSION['username']) ? $_SESSION['username'] : '';
}

function getFullName()
{
    return isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';
}

// Function to check if the user is logged out
function isLoggedOut()
{
    return !isset($_SESSION['username']);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include 'header.php'; ?>
    <style>
        :root {
            --primary-color: #4CAF50;  /* Green */
            --secondary-color: #45a049; /* Darker green */
            --accent-color: #81c784;   /* Light green */
            --text-color: #333333;
            --light-bg: #f5f7fa;
            --border-color: #E8E8E8;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Quicksand', 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: var(--light-bg);
            margin: 0;
            padding: 0;
        }

        /* Header Styles */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.8rem 2rem;
            text-align: center;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }

        /* Main Hero Section */
        .hero-section {
            background:
                        url('/Try2/images/pexels-yaroslav-shuraev-9490217.jpg');
            background-size: cover;
            background-position: center;
            padding: 6rem 2rem;
            text-align: center;
            color: white;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        /* Quick Access Section */
        .quick-access {
            padding: 4rem 2rem;
            background: var(--light-bg);
        }

        .quick-access-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .quick-access-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease;
            text-align: center;
            padding-bottom: 2rem;
        }

        .quick-access-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            margin-bottom: 1.5rem;
        }

        .quick-access-card h3 {
            margin: 1rem 0;
            color: var(--primary-color);
        }

        .quick-access-card p {
            margin-bottom: 1.5rem;
            padding: 0 1rem;
        }

        /* FAQ Section */
        .faq-section {
            padding: 4rem 2rem;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            color: var(--primary-color);
            font-size: 2.2rem;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: var(--text-color);
            font-size: 1.1rem;
            opacity: 0.8;
        }

        .faq-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
        }

        .faq-content {
            padding: 2rem;
        }

        .accordion {
            margin-bottom: 2rem;
        }

        .accordion-item {
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: var(--box-shadow);
        }

        .accordion-header {
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0;
            cursor: pointer;
        }

        .accordion-content {
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-top: none;
            border-radius: 0 0 10px 10px;
        }

        .faq-image {
            padding: 2rem;
        }

        .faq-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
        }

        /* Contact Form */
        .contact-section {
            padding: 4rem 2rem;
            background: var(--light-bg);
        }

        .contact-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
        }

        .contact-form {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: var(--box-shadow);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        /* Map */
        .map-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #3e8e41;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-secondary {
            background: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background: #3e8e41;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(69, 160, 73, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .quick-access-grid {
                grid-template-columns: 1fr;
            }

            .contact-grid {
                grid-template-columns: 1fr;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .faq-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="welcome-banner">
        Welcome to SALCC Room Booking System 
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Find Your Perfect Study Space</h1>
            <p>Book classrooms, labs, and study spaces at Sir Arthur Lewis Community College with just a few clicks!</p>
            <a href="booking.php" class="btn btn-secondary">Start Booking</a>
        </div>
    </section>

    <!-- Quick Access -->
    <section class="quick-access">
        <div class="quick-access-grid">
            <div class="quick-access-card">
                <img src="images\hjfl_alrps_workshop-14.jpg" alt="SALCC Main">
                <h3>SALCC Website</h3>
                <p>Visit the official SALCC website</p>
                <a href="https://www.salcc.edu.lc/" target="_blank" class="btn btn-outline">Visit Site</a>
            </div>
            <div class="quick-access-card">
                <img src="images\SPORTS_CLUBS_2024-22.jpg" alt="Moodle">
                <h3>SALCC Moodle</h3>
                <p>Access your online learning platform</p>
                <a href="https://moodle.salcc.edu.lc/login/index.php" target="_blank" class="btn btn-outline">Go to Moodle</a>
            </div>
            <div class="quick-access-card">
                <img src="images\WIDC_2024-43.jpg" alt="Student Portal">
                <h3>Student Portal</h3>
                <p>Access student services and information</p>
                <a href="https://www.salcc.edu.lc/student-resources" target="_blank" class="btn btn-outline">Student Portal</a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="section-title">
            <h2>Frequently Asked Questions</h2>
            <p>Find answers to common questions about room booking</p>
        </div>
        <div class="faq-grid">
            <div class="faq-content">
                <div class="accordion">
                    <div class="accordion-item">
                        <h3 class="accordion-header">How do I reserve a room for studying?</h3>
                        <div class="accordion-content">
                            <p>To reserve a room for studying, log in to your account and navigate to the Booking page. Select the date and time you wish to reserve a room for, and follow the prompts to complete the reservation process.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header">What types of rooms are available for study?</h3>
                        <div class="accordion-content">
                            <p>We offer a variety of rooms suitable for studying, including individual study rooms, group study rooms, and project rooms. You can select the type of room that best fits your needs when making a reservation.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header">How far in advance can I book a room?</h3>
                        <div class="accordion-content">
                            <p>You can book a room for studying up to two days in advance. We recommend booking early to secure your preferred date and time.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header">Can I cancel or modify my room reservation?</h3>
                        <div class="accordion-content">
                            <p>Yes, you can cancel your room reservation before your scheduled booking time. Log in to your account to make changes to your reservation.</p>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header">What amenities are available in the study rooms?</h3>
                        <div class="accordion-content">
                            <p>Our study rooms are equipped with comfortable seating, desks, power outlets, and high-speed internet access. Some rooms may also have whiteboards or projectors for collaborative work.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="faq-image">
                <img src="images\pexels-anna-shvets-3727468.jpg" alt="FAQ Image" />
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-grid">
            <div class="contact-form">
                <h2>Need Help?</h2>
                <p>We're here to assist you with your booking needs</p>
                <form method="post" action="send_email.php">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" placeholder="Your name" required 
                            <?php if(isLoggedIn()) echo 'value="' . htmlspecialchars(getFullName()) . '"'; ?>>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Your email" required 
                            <?php if(isLoggedIn()) echo 'value="' . htmlspecialchars($_SESSION['email']) . '"'; ?>>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="What's this about?" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="How can we help?" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%">Send Message</button>
                </form>
            </div>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15485.318966296772!2d-60.9958352!3d13.998452!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8c40671cbeb8f549%3A0xeaeeb7dbe7de6a69!2sSir%20Arthur%20Lewis%20Community%20College!5e0!3m2!1sen!2s!4v1713290000497!5m2!1sen!2s" 
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
    <?php include('scroll_to_top.php'); ?>
    <script src="/try2/main.js"></script>
</body>
</html>