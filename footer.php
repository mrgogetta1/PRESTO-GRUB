<head>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<style>
.footer {
    background: linear-gradient(45deg, #2e8b57, #042d86);
    color: #fff;
    padding: 18px 20px;
    font-family: Arial, sans-serif;
    margin: 0; /* Remove any extra margin */

    @media (max-width: 400px){
      width: 100%;
      padding: 0;
      /* margin-left: 100px */
  }

  @media (max-width: 400px){
      
      margin-left: -2px
  }
}

.footer-content {
    display: flex;
    justify-content: space-around;
    align-items: flex-start;
    gap: 60px;
    margin-top: 0; /* Remove margin between map and content */

    @media (max-width: 400px){
      display: block;
      gap: 10px;
  }
}

.contact-info p {
    display: flex;
    align-items: center;
    margin: 20px -155px;
    font-size: 17px;

    @media (max-width: 400px){
      position: relative;
      left: 20px;
      font-size: 13px;
  }
}

.contact-info a {
    color: #white;
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
    margin-top: 20px; /* Remove margin to bring it closer to the map */
    padding-top: 0; /* Remove any padding on top */

    @media (max-width: 400px){
      margin-top: 50px;
  }
}

.about-company p {
    font-size: 16px;
    margin-bottom: 15px;
    line-height: 1.6;
    position: relative;
    top: -30px;
}

.social-icons a {
    margin: 10px 10px;
    font-size: 26px;
    color: #fff;
    text-decoration: none;
    position: relative;
    top: -10px;
    left: -30px;
    color: #fff;
    width: 100px;
    padding: 20px;
    height: 100px;
}

.social-icons a:hover {
    color: black;
}

.map-container {
    width: 100%;
    height: 250px; /* Adjust height if needed */
    margin-bottom: 0; /* Remove margin between map and footer content */
}

@media (max-width: 1024px) {

    .footer-content{
        width: 400px;
        position: relative;
        left: 200px;
        gap: 200px;
    }

    .contact-info {
        margin-top: 30px;
    }
    
}

@media (max-width: 860px) {

.footer-content{
    width: 350px;
    position: relative;
    left: 150px;
    gap: 200px;
}

.contact-info {
    margin-top: 30px;
}

}

@media (max-width: 768px) {

.footer-content{
    width: 100%;
    position: relative;
    left: 150px;
    gap: 100px;
}

.about-company {
    padding-right: 150px;
}

.contact-info {
    margin-top: 30px;
}

}

@media (max-width: 640px) {

.footer-content{
    width: 350px;
    position: relative;
    display: block !important;
}


.contact-info {
    margin-top: 30px;
    display: block !important;
}

.about-company {
    display: block;
    position: relative;
    right: 100px;
}

}

@media (max-width: 320px) {
.footer {
    max-width: 80%;
}

.footer-content {
    position: relative;
    left: 10px;
    padding: 10px
}

.about-company {

}
}

@media (max-width: 375px) {

.footer {
    position: relative;
    left: -120px;
    width: 340px !important;
}
}

@media (max-width: 385px) {

.footer {
    position: relative;
    left: -120px;
    width: 340px !important;
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
            <a href="https://www.facebook.com/TaguigCityUniversity" target="_blank">Taguig City University</a></p>
            
            <p><span class="icon-circle"><i class="fas fa-phone"></i></span>+63 919 837 2353 </p>
        </div>
        <div class="about-company">
            <h3>About our System</h3>
            <br><p>PrestoGrub empowers users to place orders remotely 
                <br> directly from classrooms and within the school campus. 
                </div>