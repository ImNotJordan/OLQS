# OLQS Admin System Setup Guide

## Overview

The Online Learning & Quiz System now includes a comprehensive admin panel for managing user accounts, verifying registrations, and monitoring system activity.

## New Features

### 1. Account Verification System
- All new user registrations require admin approval before they can login
- Users are notified that their account is pending verification
- Admin can approve or reject registrations from the admin dashboard

### 2. Admin Module
- Dedicated admin dashboard with system statistics
- User management interface
- Account verification management
- System reports and analytics
- Admin settings and profile management

### 3. Admin Roles
- **Admin**: Full system access, can verify accounts, manage users, view reports
- **Teacher**: Can create lessons and quizzes (requires verification)
- **Student**: Can take quizzes and view lessons (requires verification)

## Setup Instructions

### Step 1: Initialize Database
Run the database setup script to create all tables with the new verification fields:

```
http://localhost/OLQS/db_setup.php
```

This will create the updated `users` table with:
- `is_verified` - Boolean field for account verification status
- `verification_token` - Token for account verification
- `verified_at` - Timestamp of verification
- `user_type` - Now includes 'admin' as an option

### Step 2: Create Admin Account
Navigate to the admin account creation page:

```
http://localhost/OLQS/create_admin.php
```

Fill in the following information:
- **Full Name**: Your full name
- **Username**: Admin username (e.g., "admin")
- **Email**: Admin email address
- **Password**: Strong password (min 6 characters)
- **Confirm Password**: Repeat password

Click "Create Admin Account" to complete setup.

### Step 3: Admin Login
Once the admin account is created, login at:

```
http://localhost/OLQS/admin_login.php
```

Use your admin credentials to access the admin dashboard.

## Admin Dashboard Features

### Dashboard Overview
The main admin dashboard displays:
- **Total Users**: Count of all registered users
- **Verified Users**: Count of approved users
- **Pending Verification**: Count of users awaiting approval
- **Teachers**: Count of teacher accounts
- **Students**: Count of student accounts

### Pending Verifications
View and manage pending account approvals:
- List of users awaiting verification
- User details (username, email, full name, type)
- Registration date
- Action buttons to approve or reject

### User Management
- View all registered users
- Filter by user type (teacher/student)
- View verification status
- Monitor user activity

### Account Verifications
Dedicated page for managing account approvals:
- **Pending Verifications**: Users awaiting approval
- **Verified Users**: List of approved users
- Approve/Reject functionality
- View verification history

### System Reports
View system-wide statistics:
- Total lessons created
- Total quizzes created
- Total quiz attempts
- Average student scores
- System overview

### Admin Settings
Manage admin profile:
- Update full name and email
- Change password
- View system information

## User Registration Flow

### Before Admin System
1. User registers
2. User can immediately login
3. User accesses system

### After Admin System
1. User registers (account created with `is_verified = FALSE`)
2. Admin receives notification of pending verification
3. Admin reviews and approves/rejects account
4. If approved: User can login
5. If rejected: Account is deleted

## Login Workflow

### User Login
```
http://localhost/OLQS/login.php
```
- Enter username and password
- System checks if account is verified
- If verified: Login successful
- If pending: Error message "Account pending admin verification"

### Admin Login
```
http://localhost/OLQS/admin_login.php
```
- Enter admin username and password
- Access admin dashboard
- Manage user accounts and system settings

## Default Admin Credentials

After running `create_admin.php`, use the credentials you created:
- **Username**: (as entered during setup)
- **Password**: (as entered during setup)

**Important**: Keep these credentials secure!

## Admin Responsibilities

### Daily Tasks
1. Check pending verifications regularly
2. Approve legitimate user accounts
3. Reject suspicious or invalid registrations
4. Monitor system activity

### Weekly Tasks
1. Review system reports
2. Check user statistics
3. Monitor quiz performance
4. Verify data integrity

### Monthly Tasks
1. Generate comprehensive reports
2. Archive old data if needed
3. Update system settings
4. Review security logs

## Account Approval Best Practices

### Approve When:
- ✓ User information appears legitimate
- ✓ Email address is valid
- ✓ Account type matches user role
- ✓ No suspicious activity detected

### Reject When:
- ✗ Duplicate/spam accounts
- ✗ Invalid email addresses
- ✗ Suspicious user information
- ✗ Inappropriate account details

## Security Features

### Account Verification
- Prevents unauthorized access
- Reduces spam accounts
- Ensures legitimate users only
- Admin oversight of all registrations

### Password Security
- Bcrypt hashing for all passwords
- Minimum 6 character requirement
- Secure password change functionality
- Admin password management

### Session Management
- Secure session handling
- Automatic timeout after 1 hour
- Role-based access control
- Admin-only pages protected

## Troubleshooting

### Problem: Cannot access admin dashboard
**Solution**: 
- Verify you're logged in as admin
- Check that your account has admin role
- Clear browser cookies and login again

### Problem: Cannot create admin account
**Solution**:
- Ensure database is initialized (run db_setup.php)
- Check that no admin account already exists
- Verify all required fields are filled

### Problem: User cannot login after approval
**Solution**:
- Verify account is marked as verified in database
- Check that user is using correct credentials
- Clear browser cache and try again

### Problem: Pending verifications not showing
**Solution**:
- Refresh the page
- Check database connection
- Verify users are actually pending approval

## File Structure

```
OLQS/
├── admin/
│   ├── dashboard.php      - Main admin dashboard
│   ├── users.php          - User management
│   ├── verifications.php  - Account verification
│   ├── reports.php        - System reports
│   └── settings.php       - Admin settings
├── admin_login.php        - Admin login page
├── create_admin.php       - Admin account creation
├── config.php             - Updated with admin functions
├── db_setup.php           - Updated database schema
├── login.php              - Updated with verification check
└── register.php           - Updated with verification token
```

## Database Changes

### Users Table Updates
```sql
ALTER TABLE users ADD COLUMN is_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN verification_token VARCHAR(255);
ALTER TABLE users ADD COLUMN verified_at TIMESTAMP NULL;
ALTER TABLE users MODIFY user_type ENUM('teacher', 'student', 'admin');
```

## Next Steps

1. ✓ Run `db_setup.php` to initialize database
2. ✓ Run `create_admin.php` to create admin account
3. ✓ Login to `admin_login.php` with admin credentials
4. ✓ Review pending user registrations
5. ✓ Approve/reject accounts as needed
6. ✓ Monitor system activity and reports

## Support

For issues or questions about the admin system:
1. Check the troubleshooting section above
2. Review system logs for errors
3. Verify database connectivity
4. Check file permissions on admin folder

## Version Information

- **System**: OLQS v1.0 with Admin Module
- **Database**: MySQL 5.7+
- **PHP**: 7.4+
- **Release Date**: 2025

---

**Admin System Setup Complete!** 🎉

Your OLQS now has a complete admin system with account verification and user management capabilities.
