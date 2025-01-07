<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .footer {
            background: linear-gradient(45deg, #2e8b57, #042d86);
            color: #fff;
            padding: 18px 20px;
            font-family: Arial, sans-serif;
            margin: 0;
          
            position: relative;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .contact-info p,
        .about-company p {
            display: flex;
            align-items: center;
            margin: 10px 0;
            font-size: 17px;
        }

        .contact-info a {
            color: white;
            text-decoration: none;
            margin-left: 10px;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        .icon-circle {
            background-color: #335533;
            color: #fff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 16px;
        }

        .about-company h3 {
            font-size: 25px;
            color: white;
            font-weight: 800;
            margin: 20px 0 10px;
        }

        .social-icons a {
            margin: 0 10px;
            font-size: 26px;
            color: #fff;
            text-decoration: none;
        }

        .social-icons a:hover {
            color: black;
        }

        .map-container {
            width: 100%;
            height: 250px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .contact-info p,
            .about-company p {
                justify-content: center;
                font-size: 15px;
            }

            .about-company h3 {
                font-size: 20px;
            }
        }

        @media (max-width: 400px) {
            .footer {
                padding: 10px;
            }

            .contact-info p,
            .about-company p {
                font-size: 13px;
            }

            .about-company h3 {
                font-size: 18px;
            }
        }
    </style>
</head>

<div class="footer">
    <!-- Google Maps Embed -->
    <div class="map-container">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3862.8926417698626!2d121.05254307487051!3d14.490851985982633!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397cf149d091943%3A0xbc9d88bd46e66c90!2sTaguig%20City%20University!5e0!3m2!1sen!2sph!4v1733843101956!5m2!1sen!2sph" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
    <div class="footer-content">
        <div class="contact-info">
            <p><span class="icon-circle"><i class="fas fa-map-marker-alt"></i></span> General Santos Ave, Taguig, 1632 Metro Manila</p>

            <p><span class="icon-circle"><i class="fas fa-envelope"></i></span>
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=tcuprestogrub@gmail.com" target="_blank">tcuprestogrub@gmail.com</a>
            </p>


            <p><span class="icon-circle"><i class="fab fa-facebook"></i></span>
                <a href="https://www.facebook.com/TaguigCityUniversity" target="_blank">Taguig City University</a>
            </p>

            <p><span class="icon-circle"><i class="fas fa-phone"></i></span>+63 919 837 2353 </p>
        </div>
        <div class="about-company">
            <h3>About our System</h3>
            <br>
            <p>PrestoGrub empowers users to place orders remotely
                <br> directly from classrooms and within the school campus.
        </div>

        </div>
        </div>