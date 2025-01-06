<style>

body{
  overflow-x: hidden;
} 
/* Sidebar Styling */
.sidebar {
  width: 200px;
  background: linear-gradient(45deg, #2e8b57, #042d86);
  color: white;
  padding: 20px;
  height: 100%;
  position: fixed;
  top: 0;
  left: 0px; /* Initially hidden */
  z-index: 10;
  transition: left 0.4s ease, box-shadow 0.4s ease;
  box-shadow: 5px 0 15px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  justify-content: space-between;

  @media (max-width: 400px) {
    width: 160px;
  }
}

/* Sidebar when active */
.sidebar.active {
  left: 0; /* Slide in from the left */
}

/* Sidebar Logo */
.sidebar-logo {
  font-size: 1.9rem;
  font-weight: bold;
  color: #fff;
  text-align: center;
  margin-bottom: 20px;
  margin-left: -20px;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

/* Sidebar Menu Items */
.sidebar-menu {
  list-style: none;
  padding: 0;
  margin-top: -40px;
  position: relative;
  top: -150px;

  @media (max-width: 400px) {
    margin-top: 200px;
  }
}

.sidebar-menu li {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  font-size: 1.2rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s, transform 0.2s;
}

.sidebar-menu li a {
  text-decoration: none;
  color: white; /* Set text color to white */
  display: flex;
  align-items: center;
  width: 100%;
  gap: 10px;
}

.sidebar-menu li a i {
  font-size: 1.4rem;
  color: white; /* Set icon color to white */
}

/* Hover and Active State */
.sidebar-menu li:hover {
  background-color: rgba(255, 255, 255, 0.2);
  transform: translateX(5px);
}

.sidebar-menu li:hover a, 
.sidebar-menu li:hover i {
  color: white; /* Keep icons and text white on hover */
}

.sidebar-menu li:active {
  background-color: rgba(255, 255, 255, 0.3);
  transform: translateX(10px);
}

/* Sidebar Footer */
.sidebar-footer {
  text-align: center;
  font-size: 0.9rem;
  position: relative;
  display: flex;
  font-size: 1.2rem;
  top: -40px;
  margin-top: 10px;
  gap: 10px;
  color: rgba(255, 255, 255, 0.8);
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.sidebar-footer img {
  height: 50px;
  border-radius: 40px;
  margin-top: 10px;
}

/* Smooth Toggle Button */
.toggle-btn {
  position: relative;
  top: -5px;
  margin-top: 25px;
  margin-left: 50px;
  z-index: 20;
  font-size: 30px;
  cursor: pointer;
  border: none;
  display: none;
  color: white;
  background-color: #1b5e20;
  padding: 10px;
  border-radius: 50%;
  box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
  transition: background-color 0.3s, transform 0.2s;
}

.toggle-btn:hover {
  background-color: #2e8b57;
  transform: rotate(90deg);
}

/* When Sidebar is hidden */
.main-content.fullscreen {
  margin-left: 0; /* Fullscreen mode when sidebar is hidden */
}

/* When Sidebar is visible */
.main-content.shifted {
  margin-left: 350px; /* Matches sidebar width */

  @media (max-width: 400px) {
    margin-left: 200px;
  }
}

/* Hide the sidebar and show the toggle button when screen width <= 400px */
@media (max-width: 400px) {
  .sidebar {
    left: -200px; /* Move sidebar out of view */
  }

  .toggle-btn {
    display: block; /* Show toggle button */
    position: fixed;
    top: 10px;
    font-size: 1.2rem;
    left: -20px;
    z-index: 15;
  }

  .main-content {
    margin-left: 0; /* Remove margin when sidebar is hidden */
  }

  .main-content.shifted {
    margin-left: 200px; /* Adjust when sidebar is visible */
  }

  .sidebar-menu li {
    position: relative;
    top: 40px;
  }

}

@media (max-width: 490px) {

.sidebar {
  left: -250px; /* Move sidebar out of view */
}

.toggle-btn {
  display: block; /* Show toggle button */
  position: fixed;
  top: 10px;
  font-size: 1.2rem;
  left: -20px;
  z-index: 15;
}

.main-content {
  margin-left: 0; /* Remove margin when sidebar is hidden */
}

.main-content.shifted {
  margin-left: 200px; /* Adjust when sidebar is visible */
}

}


@media (max-width: 450px) {

.sidebar {
  left: -250px; /* Move sidebar out of view */
}

.toggle-btn {
  display: block; /* Show toggle button */
  position: fixed;
  top: 10px;
  font-size: 1.2rem;
  left: -20px;
  z-index: 15;
}

.main-content {
  margin-left: 0; /* Remove margin when sidebar is hidden */
}

.main-content.shifted {
  margin-left: 200px; /* Adjust when sidebar is visible */
}

}


  </style>




<div class="sidebar" id="sidebar">
          <h2 class="sidebar-logo">PrestoGrub</h2>
          <br>
          <ul class="sidebar-menu">
          <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="stores.php"><i class="fas fa-store"></i> Stores</a></li>
            <li><a href="meals.php"><i class="fas fa-utensils"></i> Food</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Display these items only if the user is logged in -->
                <li><a href="order.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <li><a href="order_status.php"><i class="fas fa-receipt"></i> Order Status</a></li>
            <?php endif; ?>
          </ul>
          <div class="sidebar-footer">
              <p></p>
          </div>
      </div>

      <button id="toggle-btn" class="toggle-btn">
        <i class="fas fa-bars"></i>
    </button>


