from flask import Flask, request, jsonify, session
import mysql.connector
from datetime import datetime
from functools import wraps

# Flask application setup
app = Flask(__name__)
app.secret_key = 'PrestoGrub$1'  # For user session management

# Database connection setup (use connection pooling in production)
def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="prestogrubs"
    )

# Helper function to check if the user is logged in (for route protection)
def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session:
            return jsonify({"error": "User not logged in"}), 403
        return f(*args, **kwargs)
    return decorated_function

# Helper function to track user views
def track_user_activity(user_id, product_id):
    db = get_db_connection()
    cursor = db.cursor()

    # Check if there's an existing record for the user-product combination
    cursor.execute("SELECT * FROM user_activity WHERE user_id = %s AND product_id = %s", (user_id, product_id))
    existing_record = cursor.fetchone()

    current_time = datetime.now()

    if existing_record:
        # If there's an existing record, update view_count and last_viewed
        new_view_count = existing_record[3] + 1
        cursor.execute("UPDATE user_activity SET view_count = %s, last_viewed = %s WHERE user_id = %s AND product_id = %s",
                       (new_view_count, current_time, user_id, product_id))
    else:
        # If no record, create a new one
        cursor.execute("INSERT INTO user_activity (user_id, product_id, view_count, last_viewed) VALUES (%s, %s, %s, %s)",
                       (user_id, product_id, 1, current_time))

    db.commit()
    cursor.close()
    db.close()

# Endpoint to track product views
@app.route('/track_view', methods=['POST'])
@login_required
def track_view():
    # Get product_id and user_id from the request
    product_id = request.json.get('product_id')
    user_id = session.get('user_id')  # Assuming user_id is stored in the session

    if not product_id:
        return jsonify({"error": "Product ID missing"}), 400

    track_user_activity(user_id, product_id)
    return jsonify({"message": "Product view tracked successfully"}), 200

# Endpoint to get recommended products based on user activity
@app.route('/recommendations', methods=['GET'])
@login_required
def recommendations():
    # Fetch products most viewed by the user based on user activity
    user_id = session.get('user_id')  # Assuming user_id is stored in the session

    db = get_db_connection()
    cursor = db.cursor()

    cursor.execute("""
        SELECT product_id, MAX(view_count) AS max_view_count 
        FROM user_activity 
        WHERE user_id = %s 
        GROUP BY product_id
        ORDER BY max_view_count DESC
        LIMIT 5
    """, (user_id,))

    recommended_products = cursor.fetchall()
    cursor.close()
    db.close()

    if not recommended_products:
        return jsonify({"message": "No recommendations available"}), 200

    return jsonify({"recommended_products": recommended_products}), 200

# Endpoint for user login (For demonstration, replace with your actual login logic)
@app.route('/login', methods=['POST'])
def login():
    user_id = request.json.get('user_id')
    # Simulating user authentication (replace with your logic)
    if user_id:
        session['user_id'] = user_id
        return jsonify({"message": "User logged in successfully"}), 200
    else:
        return jsonify({"error": "Missing user_id"}), 400

# Endpoint for user logout (Clear session)
@app.route('/logout', methods=['POST'])
@login_required
def logout():
    session.pop('user_id', None)
    return jsonify({"message": "User logged out successfully"}), 200

if __name__ == '__main__':
    app.run(debug=True)
