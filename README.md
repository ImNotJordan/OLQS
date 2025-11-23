# Online Learning & Quiz System (OLQS)

A comprehensive web-based learning management system that enables teachers to create and manage lessons and quizzes while allowing students to learn and assess their knowledge.

## Features

### Core Features
- **Dual Account System**: Separate accounts for teachers and students
- **Lesson Management**: Teachers can upload various file types (PDF, presentations, videos, images)
- **Quiz Creation**: Create interactive quizzes with multiple question types
- **Automatic Scoring**: Instant quiz evaluation and feedback
- **Progress Tracking**: Comprehensive analytics and student performance monitoring
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices

### Teacher Features
- Upload and organize lessons
- Create quizzes with multiple question types (multiple choice, true/false, short answer)
- Set passing scores and time limits
- View detailed student performance analytics
- Track quiz attempts and scores
- Monitor student progress

### Student Features
- Access all available lessons
- Take quizzes with instant feedback
- View detailed quiz results
- Track personal progress and scores
- View quiz history and attempt records
- Update profile information

## System Requirements

- **Server**: Apache/XAMPP
- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7 or higher
- **Browser**: Modern browser with JavaScript enabled

## Installation & Setup

### Step 1: Database Setup

1. Open your browser and navigate to `http://localhost/phpmyadmin`
2. Navigate to the OLQS project folder
3. Open `http://localhost/OLQS/db_setup.php` in your browser
4. Wait for the database tables to be created successfully

### Step 2: File Permissions

Ensure the `uploads` directory has write permissions:
```bash
chmod 755 uploads/
chmod 755 uploads/lessons/
```

### Step 3: Access the Application

1. Open your browser and go to `http://localhost/OLQS/`
2. Click "Register" to create a new account
3. Choose your account type (Teacher or Student)
4. Complete the registration and login

## User Roles

### Teacher Account
- **Username**: Any unique username
- **Access**: Teacher dashboard with full lesson and quiz management
- **Capabilities**: Upload lessons, create quizzes, view analytics

### Student Account
- **Username**: Any unique username
- **Access**: Student dashboard with lesson viewing and quiz taking
- **Capabilities**: View lessons, take quizzes, track progress

## File Structure

```
OLQS/
├── config.php                 # Database configuration
├── db_setup.php              # Database initialization
├── index.php                 # Landing page
├── login.php                 # Login page
├── register.php              # Registration page
├── logout.php                # Logout handler
├── assets/
│   └── css/
│       └── style.css         # Main stylesheet
├── teacher/
│   ├── dashboard.php         # Teacher dashboard
│   ├── lessons.php           # Lesson management
│   ├── quizzes.php           # Quiz management
│   ├── edit_quiz.php         # Quiz editor
│   ├── analytics.php         # Performance analytics
│   ├── profile.php           # Teacher profile
│   ├── quiz_results.php      # Quiz results view
│   ├── view_attempt.php      # Attempt review
│   └── download_lesson.php   # Lesson download
├── student/
│   ├── dashboard.php         # Student dashboard
│   ├── lessons.php           # Lesson listing
│   ├── quizzes.php           # Quiz listing
│   ├── take_quiz.php         # Quiz interface
│   ├── view_result.php       # Result viewing
│   ├── view_lesson.php       # Lesson viewer
│   ├── profile.php           # Student profile
│   ├── quiz_history.php      # Attempt history
│   └── download_lesson.php   # Lesson download
└── uploads/
    └── lessons/              # Uploaded lesson files
```

## Database Schema

### Users Table
- Stores teacher and student account information
- Supports role-based access control

### Lessons Table
- Stores lesson metadata and file references
- Tracks file type and size

### Quizzes Table
- Stores quiz configuration
- Includes passing score and time limit settings

### Questions Table
- Stores quiz questions
- Supports multiple question types

### Options Table
- Stores multiple choice and true/false options
- Marks correct answers

### Quiz Attempts Table
- Records student quiz attempts
- Stores scores and completion status

### Student Answers Table
- Records individual student answers
- Tracks correctness and points earned

### Progress Tracking Table
- Maintains student progress metrics
- Tracks best scores and attempt counts

## Supported File Types

- **Documents**: PDF, DOCX, DOC
- **Presentations**: PPTX, PPT
- **Videos**: MP4, AVI, MOV
- **Images**: JPG, JPEG, PNG

**Maximum File Size**: 50MB per file

## Security Features

- Password hashing using bcrypt
- SQL injection prevention with prepared statements
- XSS protection with output sanitization
- CSRF token support
- Secure session management
- Role-based access control

## Usage Guide

### For Teachers

1. **Upload a Lesson**:
   - Go to Lessons section
   - Click "Upload Lesson"
   - Fill in title, description, and select file
   - Click Upload

2. **Create a Quiz**:
   - Go to Quizzes section
   - Click "Create Quiz"
   - Set title, passing score, and time limit
   - Add questions with options
   - Save quiz

3. **View Analytics**:
   - Go to Analytics section
   - View quiz performance and student statistics
   - Track pass rates and average scores

### For Students

1. **View Lessons**:
   - Go to Lessons section
   - Click on any lesson to view
   - Download files if needed

2. **Take a Quiz**:
   - Go to Quizzes section
   - Click "Take Quiz" on desired quiz
   - Answer all questions
   - Submit for instant scoring

3. **Track Progress**:
   - View dashboard for statistics
   - Check quiz history for past attempts
   - Monitor average scores

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check database credentials in `config.php`
- Ensure database exists

### File Upload Issues
- Check `uploads/` directory permissions
- Verify file size is under 50MB
- Confirm file type is supported

### Login Issues
- Clear browser cookies
- Verify username and password
- Check user account exists

## Features Beyond Basic Requirements

- **Advanced Analytics**: Detailed performance reports and statistics
- **Multiple Question Types**: Support for multiple choice, true/false, and short answer
- **File Management**: Support for various file types including videos
- **Progress Tracking**: Comprehensive student progress monitoring
- **Responsive UI**: Mobile-friendly interface
- **Session Management**: Secure user sessions with timeout
- **Profile Management**: Users can update profile and change password
- **Quiz History**: Students can view all past attempts
- **Detailed Feedback**: Comprehensive quiz result reviews

## Support

For issues or questions, please contact the development team or refer to the system documentation.

## License

This project is developed for educational purposes.

---

**Version**: 1.0  
**Last Updated**: 2025
