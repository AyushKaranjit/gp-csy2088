# DOKO E-Commerce Website - Viva Questions & Answers
**Duration: 5 Minutes | Team: Graduation**

---

## Technical Questions & Answers

### Q1: What challenges did you face while building the database structure?

**Answer**: "The biggest challenge was designing the relationships between tables. I had to think carefully about how products, categories, users, and orders connect to each other. For example, I needed to decide whether to store cart items in the database or just in sessions. I chose database storage so users don't lose their cart when they close the browser. I also struggled with making sure the database queries were fast enough, especially when showing products with their categories and calculating cart totals."

### Q2: How did you handle user authentication and security?

**Answer**: "Security was really important to me since this deals with user data. I used PHP's built-in password hashing functions to store passwords safely - never storing them in plain text. For user sessions, I made sure to regenerate session IDs after login to prevent session hijacking. I also added input validation on both frontend and backend to prevent SQL injection attacks. All user inputs are cleaned and validated before being used in database queries."

### Q3: What was the most difficult feature to implement?

**Answer**: "The shopping cart system was definitely the trickiest part. I had to make it work smoothly - when someone adds an item, the cart should update immediately without refreshing the page. I used JavaScript and AJAX to communicate with the PHP backend. The challenging part was making sure the cart stays synchronized between the database and what the user sees on screen. I also had to handle edge cases like what happens if someone tries to add more items than we have in stock."

### Q4: How did you make the website responsive for mobile devices?

**Answer**: "I used CSS media queries to make different layouts for different screen sizes. The main challenge was making the product grid look good on both large monitors and small phone screens. I had to adjust things like navigation menus - on mobile, I made them collapse into a hamburger menu. The checkout form was particularly tricky because it has many fields, so I had to stack them vertically on small screens and use larger touch-friendly buttons."

### Q5: Why did you choose PHP over other technologies?

**Answer**: "I chose PHP because it's widely used for e-commerce websites and has good support for database operations. It's also easy to deploy on most web servers. I considered using Node.js or Python, but PHP felt more straightforward for handling forms, file uploads, and database connections. Plus, many popular e-commerce platforms like WooCommerce use PHP, so I thought it would be good to learn."

---

## Project Management Questions & Answers

### Q6: How did you plan and organize your development process?

**Answer**: "I started by sketching out all the pages I needed and what features each page should have. Then I created a simple database design on paper before coding anything. I built the website step by step - first the basic HTML pages, then added PHP for dynamic content, then JavaScript for interactive features. I kept a simple checklist of features and checked them off as I completed them. I also tested each feature as I built it rather than waiting until the end."

### Q7: What would you do differently if you started this project again?

**Answer**: "I would spend more time planning the API structure at the beginning. I ended up rewriting some of my API endpoints because I didn't think through all the use cases initially. I would also set up automated testing from the start instead of just manual testing. And I'd probably use a CSS framework like Bootstrap to speed up the styling process, though I'm glad I learned to write custom CSS."

### Q8: How did you handle version control and backups?

**Answer**: "I used Git to track all my changes, which saved me several times when I broke something and needed to go back. I made commits after completing each small feature so I could always return to a working version. I also kept regular backups of my database, especially after adding sample data. This was really important when I accidentally deleted some test products while working on the admin panel."

---

## Feature-Specific Questions & Answers

### Q9: How does the image upload system work for products?

**Answer**: "When someone uploads a product image through the admin panel, PHP handles the file upload and saves it to the uploads folder. I added validation to make sure only image files are uploaded and that they're not too large. The system generates a unique filename to avoid conflicts if two images have the same name. I also added basic image resizing using PHP's image functions to keep file sizes reasonable for web loading."

### Q10: Explain how the search functionality works.

**Answer**: "The search feature uses SQL queries with the LIKE operator to find products that match what the user types. I search through product names and descriptions. The challenging part was making it fast enough - if someone searches for 'apple', it should show results quickly. I added database indexes on the columns I search most often. I also made the search work with partial matches, so typing 'app' will still find 'apple'."

### Q11: How do you calculate shipping costs and taxes?

**Answer**: "Right now, I have a simple system where delivery is free for orders over Rs. 1000, and Rs. 100 for smaller orders. For a real deployment, I would need to integrate with actual shipping providers to get accurate costs based on distance and weight. Taxes are calculated as a percentage of the order total. I made these configurable in the code so they can be easily changed without modifying multiple files."

---

## Problem-Solving Questions & Answers

### Q12: What bugs did you encounter and how did you fix them?

**Answer**: "One frustrating bug was when the cart total wasn't updating correctly. It turned out I was mixing up JavaScript variables and PHP variables in my calculations. I fixed it by being more careful about which calculations happen on the frontend versus backend. Another issue was with image paths - sometimes images wouldn't show up because I was using the wrong relative paths. I solved this by creating a helper function to generate correct image URLs."

### Q13: How would you scale this website for more users?

**Answer**: "For more users, I'd need to optimize the database queries and maybe add caching for frequently accessed data like product listings. I'd also need a content delivery network (CDN) for images to load faster. The current code could handle a moderate number of users, but for thousands of concurrent users, I'd need to consider things like load balancing and database optimization. I'd also add proper logging to monitor performance and catch issues early."

### Q14: What accessibility features did you consider?

**Answer**: "I tried to make the website accessible by using proper HTML structure with headings and alt text for images. I made sure the color contrast is good enough for people with vision difficulties. The website works with keyboard navigation, not just mouse clicks. However, I know there's more I could do, like adding ARIA labels for screen readers and making sure all interactive elements are properly labeled."

---

## Future Enhancement Questions & Answers

### Q15: What features would you add next?

**Answer**: "I'd love to add a review system where customers can rate and comment on products. I'd also like to implement email notifications for order updates and a more sophisticated recommendation system that suggests products based on what customers have bought before. Push notifications for mobile users and integration with social media for sharing favorite products would also be great additions."

---

## Quick-Fire Technical Terms

**If asked to explain briefly:**

- **API**: "Application Programming Interface - it's how the frontend JavaScript talks to the backend PHP"
- **AJAX**: "Asynchronous JavaScript - lets us update parts of the page without refreshing everything"
- **SQL Injection**: "A security attack where someone tries to run database commands through form inputs"
- **Session**: "How we remember that a user is logged in as they move between pages"
- **Responsive Design**: "Making the website look good on phones, tablets, and computers"

---

## Confidence Tips:

1. **Speak naturally** - avoid overly technical jargon
2. **Be honest** about challenges - it shows you learned from them
3. **Give specific examples** rather than general statements
4. **If you don't know something**, say "That's a great question, I hadn't considered that aspect yet"
5. **Show enthusiasm** for the project and what you learned
