<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

require_once 'connection/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in. Please log in again.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch sellers based on the buyer's orders and statuses (only showing active orders)
$query = "
    SELECT DISTINCT 
        u.id AS seller_id, 
        u.username AS seller_name, 
        u.profile_picture, 
        s.store_name,  -- Fetch the store name
        o.status AS order_status,
        o.order_id -- Added order_id to fetch with the order
    FROM orders o
    JOIN products p ON o.product_id = p.product_id
    JOIN stores s ON p.store_id = s.store_id
    JOIN users u ON s.user_id = u.id
    WHERE o.user_id = ? 
      AND o.status IN ('Checked Out', 'Pending', 'Delivering')  -- Exclude 'Completed' and 'Cancelled'
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sellers = $stmt->get_result();

// Group sellers by store to display one profile per store
$stores = [];
while ($seller = $sellers->fetch_assoc()) {
    $storeId = $seller['store_name'];
    if (!isset($stores[$storeId])) {
        $stores[$storeId] = [
            'seller_id' => $seller['seller_id'],
            'seller_name' => $seller['seller_name'],
            'profile_picture' => !empty($seller['profile_picture']) ? '/phpprogram/presto semi done/uploads/' . htmlspecialchars($seller['profile_picture']) : '/phpprogram/presto semi dont/uploads/default.png',
            'store_name' => htmlspecialchars($seller['store_name']),
            'order_id' => $seller['order_id']
        ];
    }
}
?>

<style>
/* Styling for the chat interface */
#liveChatSupport {
    position: relative;
    bottom: 20px;
    left: 1620px ;
    width: 50px;
    height: 50px;
    top: -450px;
    background-color: #4CAF50;
    color: white;
    text-align: center;
    border-radius: 50%;
    cursor: pointer;
}

.chatbox {
    position: fixed;
    bottom: 80px;
    right: 20px;
    width: 400px;
    height: 500px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    display: none;
    flex-direction: row;
    overflow: hidden;
}

.chatbox-sidebar {
    background-color: #4b4b4b;
    width: 120px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
    padding: 10px;
    color: white;
    overflow-y: auto;
}

.user {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    cursor: pointer;
}

.user-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #4b4b4b;
    margin-bottom: 5px;
}

.chatbox-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    background-color: #e9e9e9;
    height: 100%;
    position: relative;
}

.chatbox-header {
    background-color: #4CAF50;
    color: white;
    padding: 10px;
    font-size: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.messages {
    flex-grow: 1;
    padding: 20px;
    overflow-y: scroll;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message {
    display: flex;
    align-items: center;
    gap: 10px;
}

.message.left {
    justify-content: flex-start;
}

.message.right {
    justify-content: flex-end;
}

.message span {
    padding: 5px 10px;
    background-color: #d3d3d3;
    border-radius: 10px;
    color: #333;
}

#chatForm {
    position: absolute;
    bottom: 0;
    width: 100%;
    display: flex;
    padding: 10px;
    background-color: #f5f5f5;
    border-top: 1px solid #ccc;
    align-items: center;
    gap: 10px;
    box-sizing: border-box;
}

#chatInput {
    flex-grow: 1;
    height: 40px;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
}

#sendMessage {
    padding: 10px 20px;
    background-color: #6b4ca3;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

#sendMessage:hover {
    background-color: #593a8b;
}

.message.status {
    background-color: #f0f8ff;
    color: #333;
    font-style: italic;
}

@media (max-width: 768px) {
    #liveChatSupport {
        position: absolute;
        left: -50px;
    }
}




</style>

<!-- HTML Structure for the Chat -->
<div id="liveChatSupport" class="chat-circle">
    <p>💬</p> <!-- Chat Icon -->
</div>

<div id="chatbox" class="chatbox">
    <div class="chatbox-sidebar">
        <?php foreach ($stores as $store) { ?>
        <div class="user" data-seller-id="<?= $store['seller_id'] ?>" data-store-name="<?= $store['store_name'] ?>" data-order-id="<?= $store['order_id'] ?>">
            <div class="user-icon">
                <img src="<?= $store['profile_picture'] ?>" alt="<?= $store['store_name'] ?>" width="40" height="40">
            </div>
            <span><?= $store['store_name'] ?></span>
        </div>
        <?php } ?>
    </div>

    <div class="chatbox-content">
        <div class="chatbox-header">
            <span>Live Chat</span>
            <button id="closeChat" class="close-btn">✖</button>
        </div>
        <div class="messages" id="messages"></div>
        <form id="chatForm">
            <textarea id="chatInput" name="message" placeholder="Type a message..." autocomplete="off"></textarea>
            <input type="hidden" id="recipientId" name="recipient_id">
            <input type="hidden" id="orderId" name="order_id"> <!-- Hidden input to store order ID -->
            <button type="submit" id="sendMessage">Send</button>
        </form>
    </div>
</div>

<!-- JavaScript for Chat -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatButton = document.getElementById('liveChatSupport');
    const chatbox = document.getElementById('chatbox');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const messagesContainer = document.getElementById('messages');
    const recipientInput = document.getElementById('recipientId');
    const orderIdInput = document.getElementById('orderId');
    let sellerId = null;
    let storeName = null;
    let lastMessageId = 0; // Store the ID of the last fetched message
    let fetchInterval = null;

    // Toggle chatbox visibility
    chatButton.addEventListener('click', function () {
        const currentDisplay = chatbox.style.display;
        chatbox.style.display = (currentDisplay === 'none' || currentDisplay === '') ? 'flex' : 'none';
    });

    // Close the chatbox when the close button is clicked
    document.getElementById('closeChat').addEventListener('click', function () {
        chatbox.style.display = 'none';
        if (fetchInterval) {
            clearInterval(fetchInterval); // Stop fetching messages when chatbox is closed
        }
    });

    // Fetch messages for the seller/store
    function fetchMessages(sellerId) {
        if (!sellerId) return;

        fetch(`api_handler.php?action=fetchMessages&sender_id=${<?= json_encode($user_id) ?>}&recipient_id=${sellerId}&last_message_id=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.messages.forEach(msg => {
                        const msgElem = document.createElement('div');
                        msgElem.className = msg.sender_id === <?= json_encode($user_id) ?> ? 'message right' : 'message left';
                        msgElem.innerHTML = `<span>${msg.message}</span>`;
                        messagesContainer.appendChild(msgElem);
                        lastMessageId = Math.max(lastMessageId, msg.id); // Update last message ID
                    });
                    messagesContainer.scrollTop = messagesContainer.scrollHeight; // Scroll to the bottom
                } else {
                    console.error('Failed to fetch messages:', data.error);
                }
            })
            .catch(err => console.error('Error fetching messages:', err));
    }

    // Set seller ID and store name when clicking on a seller's profile
    document.querySelectorAll('.user').forEach(user => {
        user.addEventListener('click', function () {
            sellerId = this.dataset.sellerId;
            storeName = this.dataset.storeName;
            recipientInput.value = sellerId;

            const orderId = this.dataset.orderId; // Fetch the correct order ID from the clicked seller
            orderIdInput.value = orderId; // Set the order ID dynamically in the hidden input field

            lastMessageId = 0; // Reset lastMessageId when switching users
            messagesContainer.innerHTML = ''; // Clear previous messages
            fetchMessages(sellerId);

            if (fetchInterval) {
                clearInterval(fetchInterval); // Clear previous interval
            }

            // Set up the interval to fetch new messages every 3 seconds
            fetchInterval = setInterval(() => {
                fetchMessages(sellerId);
            }, 3000);
        });
    });

    // Handle message submission
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent form submission

        const message = chatInput.value.trim();
        const recipientId = recipientInput.value;
        const orderId = orderIdInput.value;

        if (message === '') {
            return;
        }

        // Send the message to the server
        fetch('api_handler.php?action=sendMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `message=${encodeURIComponent(message)}&recipient_id=${recipientId}&order_id=${orderId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Append the new message to the chat
                    const newMessage = data.message;
                    const newMessageElem = document.createElement('div');
                    newMessageElem.className = newMessage.sender_id === <?= json_encode($user_id) ?> ? 'message right' : 'message left';
                    newMessageElem.innerHTML = `<span>${newMessage.message}</span>`;
                    messagesContainer.appendChild(newMessageElem);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight; // Scroll to the bottom

                    // Clear the input field
                    chatInput.value = '';
                } else {
                    alert('Error sending message: ' + data.error);
                }
            })
            .catch(err => console.error('Error sending message:', err));
    });
});


</script>