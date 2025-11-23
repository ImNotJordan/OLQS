# OLQS - Quick Start Guide

## 🚀 Getting Started in 3 Steps

### Step 1: Initialize Database
```
Open: http://localhost/OLQS/db_setup.php
Wait for success message
```

### Step 2: Create Account
```
Go to: http://localhost/OLQS/
Click: Register
Choose: Teacher or Student
Complete: Registration form
```

### Step 3: Start Using
```
Login with your credentials
Explore the dashboard
```

---

## 👨‍🏫 For Teachers

### Upload a Lesson
1. Click **Lessons** in sidebar
2. Click **Upload Lesson** button
3. Fill in title and description
4. Select a file (PDF, video, presentation, etc.)
5. Click **Upload**

### Create a Quiz
1. Click **Quizzes** in sidebar
2. Click **Create Quiz** button
3. Enter quiz title and passing score
4. Click **Create**
5. Click **Edit** to add questions
6. Add questions with options
7. Mark correct answers
8. Save

### View Student Performance
1. Click **Analytics** in sidebar
2. View quiz performance statistics
3. See student performance table
4. Click on quiz results to see individual attempts

---

## 👨‍🎓 For Students

### View Lessons
1. Click **Lessons** in sidebar
2. Browse available lessons
3. Click **View Lesson** to open
4. Download if needed

### Take a Quiz
1. Click **Quizzes** in sidebar
2. Click **Take Quiz** on desired quiz
3. Answer all questions
4. Click **Submit Quiz**
5. View results immediately

### Check Progress
1. View **Dashboard** for statistics
2. See lessons viewed and quizzes attempted
3. Check average score
4. Click **History** on quiz to see past attempts

---

## 📊 Key Features

| Feature | Teacher | Student |
|---------|---------|---------|
| Upload Lessons | ✓ | ✗ |
| Create Quizzes | ✓ | ✗ |
| Take Quizzes | ✗ | ✓ |
| View Analytics | ✓ | ✗ |
| View Lessons | ✗ | ✓ |
| Track Progress | ✓ | ✓ |
| Manage Profile | ✓ | ✓ |

---

## 📁 Supported Files

- **Documents**: PDF, DOCX, DOC
- **Presentations**: PPTX, PPT
- **Videos**: MP4, AVI, MOV
- **Images**: JPG, JPEG, PNG

**Max Size**: 50MB per file

---

## 🔐 Security

- Passwords are encrypted
- Secure login system
- Role-based access control
- Protected file uploads

---

## ❓ Common Questions

**Q: Can students retake quizzes?**
A: Yes, students can take quizzes multiple times. The system tracks all attempts.

**Q: How are quizzes scored?**
A: Multiple choice and true/false questions are scored automatically. Short answer questions are marked but may need manual review.

**Q: Can I download lessons?**
A: Yes, both teachers and students can download lesson files.

**Q: How do I reset my password?**
A: Go to Profile → Change Password and enter your current and new password.

**Q: What happens if I run out of time on a quiz?**
A: If a time limit is set, the quiz will auto-submit when time expires.

---

## 🆘 Troubleshooting

**Can't login?**
- Check username and password
- Clear browser cookies
- Verify account was created

**Can't upload files?**
- Check file size (max 50MB)
- Verify file type is supported
- Check internet connection

**Database error?**
- Run db_setup.php again
- Ensure MySQL is running
- Check XAMPP services

---

## 📞 Support

For detailed documentation, see **README.md**
For setup help, see **SETUP_INSTRUCTIONS.txt**

---

**Version**: 1.0 | **Last Updated**: 2025
