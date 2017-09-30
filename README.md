This website and related database is designed to act as a banking website. The layout of tables and keys in our database allow for each customer to have a single account connected to their email address and each account can have multiple transactions associated with it. The relationship between the customer table and the account table is one to one, whereas the account table to transactions table is zero to many as the account will have no related transactions when a customer signs up but may have multiple transactions generated over time.
  
When a customer signs up, an account is automatically generated and associated with that user. For further development, it is possible to allow a user to have multiple accounts connected to their log in by including a foreign key in the accounts table. This was left out due to time constraints.
  
The website makes use of the model/view/controller architecture by separating the three aspects. It does this by having the model only handle the business logic and any other logic related to the database. The controller then interprets the input from the view and redirects appropriately, communicating between the model and the view. The view then displays the appropriate template to the screen.

This website attempts to minimize data duplication by separating the data into three separate tables. We have noticed that the relationship between an account and a customer is only a 1 to 1 relationship, because of this you could have 1 giant table containing all columns from both tables, this would mean that you would have to change the model schema later on if you wanted to add new functionality, such as having a customer own many accounts. 
  
Another observation we made was that when customers first register, they will have made no transactions yet and that they can make many transactions over time. Instead of having multiple lines in the customer table for each transaction, we separate the transactions and add a foreign key with the customers unique ID to minimize data duplication.

The website uses sessions to create persistence with persistence lasting until the user logs out or returns to the home page. The inability to close the webpage then return directly to the dashboard without going through the home page means even if a user closes the web page without logging out, the session is forcibly ended. 
  
All Passwords are hashed before being stored in the database as an extra level of security. Another security measure is requiring all logins to need a valid email address in order to log in. 
  

Instructions for the end user:
1.	First time visit:
  
	a.	Click “Sign up” to register as a new user.
  
	b.	Fill out all input fields marked with an asterisk and any additional fields that are optional if needed.
  
	c.	Once done, click “Register”.
2.	Returning customer:
  
	a.	Click “Login”.
  
	b.	Enter a valid email address and password
    
		i.	Enter email: “tony.stark@gmail.com” pass: 1234
  
	c.	Once done, click “Log in”.
3.	Enter a new transaction: 
  
	a.	Click “Enter Transaction”.
  
	b.	Fill out all required fields marked with an asterisk with an optional description.
    
		i.	Must enter your own account number. Account number can be found above the form. 
    
		ii.	Cannot withdraw more money than what is show as the balance (above form).
  
	c.	Click “Submit Transaction” to proceed with the transaction or “Cancel” to return to the dashboard.
4.	Once finished:
  
	a.	Click “logout” - top right corner.
