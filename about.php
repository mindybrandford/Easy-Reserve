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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>About</title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --accent-color: #81c784;
            --text-color: #333333;
            --light-bg: #f5f7fa;
            --border-color: #E8E8E8;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }

        /* Timeline Enhancement */
        .timeline-section {
            background: var(--light-bg);
            padding: 6rem 0;
            position: relative;
        }

        .timeline {
            position: relative;
            max-width: 1200px;
            margin: 4rem auto;
            padding: 2rem 0;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 4px;
            background: var(--primary-color);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -2px;
            z-index: 1;
        }

        .timeline-item {
            position: relative;
            width: calc(50% - 40px);
            margin-bottom: 4rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .timeline-item:nth-child(odd) {
            left: 0;
            padding-right: 40px;
        }

        .timeline-item:nth-child(even) {
            left: calc(50% + 40px);
        }

        .timeline-item.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .timeline-content {
            background: white;
            border-radius: 15px;
            padding: 2.5rem 2rem 2rem;
            box-shadow: var(--box-shadow);
            position: relative;
            transition: all 0.3s ease;
            overflow: visible;
        }

        .timeline-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .timeline-year {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: bold;
            z-index: 2;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            white-space: nowrap;
        }

        .timeline-content h3 {
            margin-top: 0.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            font-size: 1.4rem;
        }

        .timeline-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin: 1rem 0;
            transition: transform 0.3s ease;
        }

        .timeline-content:hover .timeline-image {
            transform: scale(1.05);
        }

        .timeline-content p {
            margin-bottom: 1rem;
            line-height: 1.6;
            color: #444;
        }

        .timeline-details {
            display: none;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .timeline-details.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        .read-more-btn {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .read-more-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .timeline::after {
                left: 31px;
            }

            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
                margin-bottom: 3rem;
            }

            .timeline-item:nth-child(odd),
            .timeline-item:nth-child(even) {
                left: 0;
            }

            .timeline-content {
                padding: 2rem 1.5rem 1.5rem;
            }

            .timeline-year {
                left: 0;
                transform: translateX(0) translateY(-50%);
                border-radius: 0 25px 25px 0;
                padding: 0.4rem 1.2rem;
            }
        }

        /* About SALCC Section */
        .about-salcc {
            padding: 6rem 2rem;
            background: white;
        }

        .salcc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
        }

        .salcc-content {
            padding: 2.5rem;
        }

        .salcc-content h3 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #1a1a1a;
        }

        .salcc-content p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .salcc-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease;
        }

        .salcc-image:hover {
            transform: scale(1.02);
        }

        /* Grid Section */
        .grid-section {
            padding: 6rem 2rem;
            background: var(--light-bg);
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .grid-item {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .grid-item:hover {
            transform: translateY(-10px);
        }

        .grid-item:hover img {
            transform: scale(1.1);
        }

        .grid-item img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .grid-item-content {
            padding: 2rem;
        }

        .grid-item-content h4 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .grid-item-content p {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #555;
        }

        /* Easy Reserve Section */
        .easy-reserve {
            padding: 6rem 2rem;
            background: white;
        }

        .reserve-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
        }

        .reserve-content {
            padding: 2.5rem;
        }

        .reserve-content h3 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #1a1a1a;
        }

        .reserve-content p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .feature-list {
            margin: 2.5rem 0;
        }

        .feature-item {
            padding: 1.2rem;
            margin-bottom: 1.2rem;
        }

        .feature-item p {
            margin: 0;
            font-size: 1.1rem;
            color: #444;
        }

        /* Lists */
        ul {
            margin: 1.5rem 0;
            padding-left: 1.5rem;
        }

        ul li {
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
            color: #444;
            line-height: 1.6;
        }

        /* Buttons */
        .interactive-button {
            font-size: 1.1rem;
            font-weight: 500;
            padding: 1rem 2rem;
            margin-top: 2rem;
        }

        /* Section title */
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 3rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        /* Section spacing */
        section {
            padding: 6rem 0;
        }

        section:not(:last-child) {
            margin-bottom: 2rem;
        }

        /* Body styles */
        body {
            line-height: 1.8;
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            margin: 0 0 1.5rem 0;
            line-height: 1.3;
            font-weight: 600;
            color: #1a1a1a;
        }

        p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            line-height: 1.8;
            color: #444;
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
                margin-bottom: 2rem;
            }

            .salcc-content, .reserve-content {
                padding: 1.5rem;
            }

            .grid-item-content {
                padding: 1.5rem;
            }

            p {
                font-size: 1rem;
            }
        }

        /* Add styles for user info */
        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: var(--box-shadow);
            color: var(--text-color);
        }
        .user-info span {
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- User Info Display -->
    <?php if(isLoggedIn()): ?>
    <div class="user-info">
        Welcome, <span><?php echo htmlspecialchars(getUserName()); ?></span>
    </div>
    <?php endif; ?>

    <!-- About SALCC Section -->
    <section class="about-salcc">
        <h2 class="section-title">About SALCC</h2>
        <div class="salcc-grid">
            <div class="salcc-content">
                <h3>St. Lucia's Premier Educational Institution</h3>
                <p>The Sir Arthur Lewis Community College (SALCC) stands as a beacon of academic excellence in St. Lucia. Named after our distinguished Nobel Laureate, the college embodies his vision of empowering minds and fostering development through education.</p>
                <p>Our institution offers a diverse range of programs in Arts, Sciences, and Technical Studies, preparing students for the challenges of tomorrow while honoring the legacy of Sir Arthur Lewis.</p>
                <a href="https://www.salcc.edu.lc/programmes/" target="_blank" class="interactive-button">Explore Our Programs</a>
            </div>
            <div class="salcc-image-container">
                <img src="https://thevoiceslu.com/wp-content/uploads/2018/01/Sir-Arthur-Lewis-Tomb-in-the-grounds-of-SALCC.jpg" alt="Sir Arthur Lewis at SALCC" class="salcc-image">
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section id="timeline" class="timeline-section">
        <h2 class="section-title">Sir Arthur Lewis: A Journey of Excellence</h2>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">1915</div>
                    <h3>Early Life in Saint Lucia</h3>
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSFaEhXQo0MXC5VfMAJqYrCBj5SIqojJruxog&s" alt="Young Sir Arthur Lewis" class="timeline-image">
                    <p>Born on January 23, 1915, in Castries, Saint Lucia, to George and Ida Lewis. Despite facing racial and colonial barriers, he showed exceptional academic abilities from an early age.</p>
                    <button class="read-more-btn">Read More</button>
                    <div class="timeline-details">
                        <p>At the age of seven, Lewis was already helping with his father's business, developing both practical and academic skills. His mathematical abilities were particularly notable, and he completed his secondary education at the age of 14, setting the stage for his remarkable academic journey.</p>
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">1932</div>
                    <h3>London School of Economics</h3>
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSm22OgUKmg5VGrcv0Ns7w157rw2gHaj8slSg&s" alt="London School of Economics" class="timeline-image">
                    <p>At just 17, Lewis earned a government scholarship to attend the prestigious London School of Economics, marking the beginning of his groundbreaking academic career.</p>
                    <button class="read-more-btn">Read More</button>
                    <div class="timeline-details">
                        <p>Despite facing racial discrimination, Lewis excelled at LSE, earning his Bachelor's degree in 1937 and his Ph.D. in 1940. His dissertation on industrial development in the British West Indies laid the groundwork for his future economic theories.</p>
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">1938</div>
                    <h3>Breaking Barriers at LSE</h3>
                    <img src="https://uceap.universityofcalifornia.edu/sites/default/files/marketing-images/program-page-images/london-school-of-economics-glance.jpg" alt="LSE" class="timeline-image">
                    <p>Made history as the first black faculty member at the London School of Economics, breaking racial barriers in academia.</p>
                    <button class="read-more-btn">Read More</button>
                    <div class="timeline-details">
                        <p>His appointment as a lecturer at LSE was groundbreaking for the time. Lewis went on to become a full professor by 1948, continuing to challenge racial prejudices while contributing significantly to economic theory. His work during this period focused on economic problems in developing countries.</p>
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">1954</div>
                    <h3>The Dual Sector Model</h3>
                    <img src="https://upload.wikimedia.org/wikipedia/en/8/84/Arthur_Lewis_%28Nobel_photo%29.jpg" alt="Lewis's Economic Model" class="timeline-image">
                    <p>Published his groundbreaking work on the "dual sector model," revolutionizing development economics.</p>
                    <button class="read-more-btn">Read More</button>
                    <div class="timeline-details">
                        <p>The Lewis Model, as it became known, explained the growth of developing countries by analyzing the evolution of the agricultural and industrial sectors. This work would later earn him the Nobel Prize and remains influential in development economics today.</p>
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-year">1979</div>
                    <h3>Nobel Prize in Economics</h3>
                    <img src="https://images.theconversation.com/files/61079/original/j4r8h8d7-1412709816.jpg?ixlib=rb-4.1.0&q=45&auto=format&w=926&fit=clip" alt="Nobel Prize Ceremony" class="timeline-image">
                    <p>Awarded the Nobel Memorial Prize in Economics for his pioneering research into economic development.</p>
                    <button class="read-more-btn">Read More</button>
                    <div class="timeline-details">
                        <p>Shared with Theodore Schultz, the Nobel Prize recognized Lewis's innovative contributions to understanding economic development, with particular focus on the problems of developing countries. His theories continue to influence development economics and policy-making globally.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sir Arthur Lewis Section -->
    <section class="grid-section">
        <h2 class="section-title">Legacy of Excellence</h2>
        <div class="grid-container">
            <div class="grid-item">
                <img src="https://static.independent.co.uk/2023/03/30/03/arthur-lewis.jpg?width=1200&height=1200&fit=crop" alt="Sir Arthur">
                <div class="grid-item-content">
                    <h4>Sir William Arthur Lewis</h4>
                    <p>Sir Arthur Lewis broke barriers as the first black professor at the London School of Economics, paving the way for future generations.</p>
                </div>
            </div>
            <div class="grid-item">
                <img src="https://scontent.fuvf1-1.fna.fbcdn.net/v/t39.30808-6/421726688_886366213282185_6993748738316657903_n.jpg?_nc_cat=109&ccb=1-7&_nc_sid=127cfc&_nc_ohc=vwVxSJnmTAMQ7kNvgGoS-px&_nc_zt=23&_nc_ht=scontent.fuvf1-1.fna&_nc_gid=AEmCJ8ZTFHNd1cI7fPYTRyF&oh=00_AYCXWKWdDMVS61bG4Ody9-wYfkCNdK-Dnu3SPl8u0_2Z2Q&oe=6757EFE3" alt="Sr Leton Thomas">
                <div class="grid-item-content">
                    <h4>Sir Leton F. Thomas</h4>
                    <p>Sir Leton Felix Thomas was the first principal of SALCC from 1987 to 1995. He was instrumental in the development of the College and Saint Lucia's education system at large.</p>
                </div>
            </div>
            <div class="grid-item">
                <img src="https://repeatingislands.files.wordpress.com/2014/02/unnamed6.jpg" alt="Legacy">
                <div class="grid-item-content">
                    <h4>Last Person</h4>
                    <p>usure what to place here yet</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Easy Reserve Section -->
    <section class="easy-reserve">
        <h2 class="section-title">SALCC Easy Reserve</h2>
        <div class="reserve-grid">
            <div class="reserve-content">
                <h3>Modern Room Booking Made Simple</h3>
                <p>SALCC Easy Reserve revolutionizes how students access campus facilities, providing a seamless booking experience that enhances academic life.</p>
                
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <p>Real-time room availability checking</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <p>Quick and easy booking process</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <p>24/7 system access</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <p>Mobile-friendly interface</p>
                    </div>
                </div>

                <h4>Future Innovations</h4>
                <p>We're committed to continuous improvement with upcoming features:</p>
                <ul>
                    <li>Smart room recommendations</li>
                    <li>Calendar integration</li>
                    <li>Real-time notifications</li>
                    <li>Advanced analytics dashboard</li>
                </ul>
                <a href="/try2/booking.php" class="interactive-button">Start Booking Now</a>
            </div>
            <div class="reserve-image-container">
                <img src="https://images.pexels.com/photos/6344239/pexels-photo-6344239.jpeg" alt="Students using Easy Reserve" class="salcc-image">
            </div>
        </div>
    </section>

   
    <script>
        // Timeline Animation
        const timelineItems = document.querySelectorAll('.timeline-item');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.2
        });

        timelineItems.forEach(item => {
            observer.observe(item);
        });

        // Read More Functionality
        document.querySelectorAll('.read-more-btn').forEach(button => {
            button.addEventListener('click', function() {
                const details = this.nextElementSibling;
                const isActive = details.classList.contains('active');
                
                // Close all other open details
                document.querySelectorAll('.timeline-details.active').forEach(detail => {
                    detail.classList.remove('active');
                });

                if (!isActive) {
                    details.classList.add('active');
                    this.textContent = 'Read Less';
                } else {
                    details.classList.remove('active');
                    this.textContent = 'Read More';
                }
            });
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
<?php include 'footer.php'; ?>
    <?php include('scroll_to_top.php'); ?>
