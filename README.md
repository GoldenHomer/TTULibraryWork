# Work Code Snippets

This repo hosts some work code (with senstive info removed) and screenshots of my UI work since authentication I have implemented for the web applications won't allow the public to access them. Let me know if you want me to explain what the code does - I would be happy to do so.
<br>

### Screenshot of ticketing application I'm working on now
Instead of Bootstrap 4, I decided to use Semantic UI just to do something different.

![Ticketing system I'm working on](ticketing.png)


### TTU Library Study Carrel Reservation submission form
The application uses getskeleton.com as the UI framework. All the input fields (except Comments) are autofilled with the respective info from TTU's eRaider authentication system. I just remove the sensitive info.

![Study Carrel Reservation](studyCarrelReservation.png)


### TTU Library Study Carrel Application - Pending Page
Here you can see my pending reservation. The Reject button removes the reservation from the SQL Server database. Accept button moves it to another table with active reservations. Let's see what happens when Contract is clicked...

![Study Carrel Reservation](my%20study%20reservation.png)


### TTU Library Study Carrel Application - Contract Modal
The Contract button brings up a contract modal, which comes from jquerymodal.com. Student has to physically come to the library to initial the contract and a staff member enters the carrel room #. Student gets an email with contract attachment when Send is clicked and contract can be seen with View button. Let's take a look at the contract that is generated with PHP.

![Study Carrel Reservation Contract Modal](contract%20modal.png)


### TTU Library Study Carrel Application - My Contract
I use tcpdf.org to generate the contract PDF taking the input values from the contract modal. Code for that can be seen [here](contract.php).

![Study Carrel Reservation Contract Modal](my%20contract.png)
