<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the current page is "index.php"
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
/* General Navbar Styling */
.navbar {
    background: green; /* Dark green background */
    height: 100px; /* Adjust height based on content */
    display: flex;
    justify-content: <?php echo $current_page == 'index.php' ? 'space-between' : 'center'; ?>; /* Center content except for the home page */
    align-items: center; /* Center items vertically */
    width: 100%; /* Full width of the container */
    padding: 0 20px; /* Padding to add spacing on left and right */
}

/* Navbar layout styling */
.navdiv {
    display: flex;
    justify-content: space-between; /* Distribute nav items evenly */
    align-items: center; /* Center the items vertically */
    width: 50%; /* Reduce width to make the navbar more compact */
    max-width: 800px; /* Max-width for the navbar */
}

@media screen and (max-width: 500px) {
            .navdiv {
                margin-bottom: 30px;
            }

            .dropdown > *{
                margin-top: 30px;
            }

            .navdiv ul li {
                position: relative;
                right: 20px
            }

            .logo image{
                position: relative;
                right: -120px;
            }
        }

nav .dropdown-content {
    display: none;
    position: absolute;
    background-color: transparent;
    min-width: 80px;
    z-index: 1;
    border-radius: 0;
}

/* Navbar list styling */
.navdiv ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: space-between; /* Even space between list items */
    width: 100%; /* Ensure full width utilization */
}

/* Styling the nav items */
.navdiv ul li {
    margin: 0 8px; /* Reduce space between items */
}

/* Styling the logo */
.logo {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-right: 30px; /* Reduced margin */
}

/* Logo Image Styling */
.logo img {
    height: 150px;
    width: 170px;
    margin-right: -35px; /* Adjusted space between logo and text */
    margin-left: 70px;
}

/* Logo Text */
.logo a {
    text-decoration: none;
    color: white;
    font-size: 28px;
    font-weight: bold;
    letter-spacing: 1px;
}

/* Styling links in the nav */
.navdiv ul li a {
    font-size: 17px; /* Reduced font size */
    color: white;
    text-decoration: none;
    display: block;
    padding: 5px 10px; /* Add some padding to make the items clickable but compact */
    font-weight: bold;
}

/* Remove hover effect for nav links */
.navdiv ul li a:hover {
    color: white; /* No color change on hover */
    transform: none; /* No scaling effect */
}

/* Dropdown button styling */
.dropdown .dropbtn {
    font-size: 16px; /* Reduced font size */
    color: white;
    background-color: transparent;
    cursor: pointer;
    margin: -5px; /* Reduced margin between dropdown and other items */
    border: 2px solid white;
    border-radius: 6px;
}

/* Remove hover effect for dropdown items */
.dropdown-content a:hover {
    background-color: transparent; /* No background color change on hover */
}

.dropdown:hover .dropdown-content {
    display: block;
}

/* Style for the search form */
#searchForm {
    display: flex;               /* Align input and button horizontally */
    align-items: center;         /* Center align the items vertically */
    justify-content: center;     /* Center the form on the page */
    width: auto;                 /* Allow the form to be flexible */
    margin: 0 32px;              /* Provide some margin to avoid overlap with other elements */
}

/* Style for the search input */
#searchInput {
    width: 220px;                /* Reduced width for better fitting */
    padding: 12px 12px;          /* Padding inside the input */
    font-size: 16px;             /* Font size */
    border: 1px solid #ccc;     /* Light gray border */
    border-radius: 30px;        /* Rounded corners */
    outline: none;              /* Remove default outline */
    transition: border 0.3s ease-in-out; /* Smooth transition for border color */
    margin-right: 10px;         /* Space between input and button */
}

/* Focus state for the input */
#searchInput:focus {
    border-color: #ff0000;       /* Red border when input is focused */
}

/* Style for the search button */
#searchButton {
    padding: 12px 20px;         /* Padding inside the button */
    font-size: 16px;            /* Font size */
    background-color: white;  /* Red background color */
    color: black;               /* White text */
    border: none;               /* Remove border */
    border-radius: 30px;        /* Rounded corners */
    cursor: pointer;            /* Pointer cursor on hover */
    transition: background-color 0.3s ease; /* Smooth transition for background color */
}

/* Hover effect for the button */
#searchButton:hover {
    background-color: lightgreen;   /* Darker red on hover */
}

/* Optional: Mobile-friendly (responsive) design */
@media (max-width: 600px) {
    #searchInput {
        width: 70%;               /* Smaller input on mobile */
    }

    #searchButton {
        width: 70%;               /* Button width also adjusts on mobile */
        margin-top: 10px;         /* Add margin for spacing */
        margin-left: 0;           /* Remove left margin */
    }

    .navbar {
        flex-direction: column;   /* Stack navbar items on mobile */
        height: auto;             /* Let the navbar height adjust based on content */
    }

    .navdiv ul {
        flex-direction: column;   /* Stack navigation items vertically */
        align-items: center;      /* Center the items */
        width: 100%;              /* Ensure the full width */
    }

    .logo img {
        height: 120px;            /* Smaller logo on mobile */
    }
}

/* Logo Text */
.logo a {
    text-decoration: none;
    color: white;
    font-size: 28px;
    font-weight: bold;
    letter-spacing: 1px;
    transition: color 0.3s ease;
    cursor: default;
}

/* Styling links in the nav */
.navdiv ul li a {
    font-size: 17px; /* Reduced font size */
    color: white;
    text-decoration: none;
    display: block;
    padding: 5px 10px; /* Add some padding to make the items clickable but compact */
    font-weight: bold;
}

.navdiv ul li a:hover {
    color: black;
    transform: scale(1.05);
}

/* Dropdown button styling */
.dropdown .dropbtn {
    font-size: 16px; /* Reduced font size */
    color: white;
    background-color: transparent;
    cursor: pointer;
    margin: -5px; /* Reduced margin between dropdown and other items */
    border: 2px solid white;
    border-radius: 6px;
}

/* Dropdown menu styling */
.dropdown-content {
    display: none;
    position: absolute;
    background-color: rgb(1, 89, 33);
    z-index: 1;
    border-radius: 5px;
}

.dropdown-content a {
    padding: 10px 20px; /* Reduced padding */
    color: white;
    text-decoration: none;
    display: block;
}

.dropdown-content a:hover {
    background-color: rgba(46, 204, 113, 0.5);
}

.dropdown:hover .dropdown-content {
    display: block;
}

/* Style for the search form */
#searchForm {
    display: flex;               /* Align input and button horizontally */
    align-items: center;         /* Center align the items vertically */
    justify-content: center;     /* Center the form on the page */
    width: auto;                 /* Allow the form to be flexible */
    margin: 0 32px;              /* Provide some margin to avoid overlap with other elements */
}

/* Style for the search input */
#searchInput {
    width: 220px;                /* Reduced width for better fitting */
    padding: 12px 12px;          /* Padding inside the input */
    font-size: 16px;             /* Font size */
    border: 1px solid #ccc;     /* Light gray border */
    border-radius: 30px;        /* Rounded corners */
    outline: none;              /* Remove default outline */
    transition: border 0.3s ease-in-out; /* Smooth transition for border color */
    margin-right: 10px;         /* Space between input and button */
}

/* Focus state for the input */
#searchInput:focus {
    border-color: #ff0000;       /* Red border when input is focused */
}

/* Style for the search button */
#searchButton {
    padding: 12px 20px;         /* Padding inside the button */
    font-size: 16px;            /* Font size */
    background-color: white;  /* Red background color */
    color: black;               /* White text */
    border: none;               /* Remove border */
    border-radius: 30px;        /* Rounded corners */
    cursor: pointer;            /* Pointer cursor on hover */
    transition: background-color 0.3s ease; /* Smooth transition for background color */
}

/* Hover effect for the button */
#searchButton:hover {
    background-color: lightgreen;   /* Darker red on hover */
}

/* Optional: Mobile-friendly (responsive) design */
@media (max-width: 600px) {
    #searchInput {
        width: 70%;               /* Smaller input on mobile */
    }

    #searchButton {
        width: 70%;               /* Button width also adjusts on mobile */
        margin-top: 10px;         /* Add margin for spacing */
        margin-left: 0;           /* Remove left margin */
    }

    .navbar {
        flex-direction: column;   /* Stack navbar items on mobile */
        height: auto;             /* Let the navbar height adjust based on content */
    }

    .navdiv ul {
        flex-direction: column;   /* Stack navigation items vertically */
        align-items: center;      /* Center the items */
        width: 100%;              /* Ensure the full width */
    }

    .logo img {
        height: 120px;            /* Smaller logo on mobile */
    }
}
</style>

<nav class="navbar">
    <div class="logo">
        <img src="uploads/prestologo.png" alt="Logo">
        <a>PrestoGrub</a>
    </div>
    <div class="navdiv">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="stores.php">Stores</a></li>
            <li><a href="meals.php">Foods</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['isAdmin'] == 0): ?>
                    <li><a href="order.php">Cart</a></li>
                    <li><a href="order_status.php">Order Status</a></li>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="dropdown">
                    <button class="dropbtn">Others▾</button>
                    <div class="dropdown-content">
                        <a href="account.php">Account</a>
                        <a href="orderhistory.php">History</a>
                        <a href="function/logout.php">Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Only show the search bar on the Home page -->
    <?php if ($current_page == 'index.php'): ?>
        <form method="GET" action="index.php" id="searchForm">
            <input type="text" name="search" placeholder="Search for products" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" id="searchInput"/>
            <button type="submit" id="searchButton">Search</button>
        </form>
    <?php endif; ?>
</nav>
