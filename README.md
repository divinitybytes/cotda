# 🏆 Chore Tracker App

A mobile-first Laravel 12 application for tracking household chores with points, verification, and a cash reward system.

## ✨ Features

### 👨‍💼 Admin Features
- **Task Management**: Create one-time and recurring tasks with point values
- **User Management**: Assign tasks to users and manage permissions
- **Verification System**: Review and approve/reject task completions with photos
- **Daily Awards**: Automatically award "Child of the Day" to top performers
- **Admin Dashboard**: Overview of users, tasks, and pending verifications

### 👦👧 User Features
- **Task Completion**: Complete tasks by uploading photos from iPhone camera
- **Points System**: Earn points for completed tasks (pending admin verification)
- **Cash Balance**: Track rolling cash balance with 1-year vesting schedule
- **Early Cash-Out**: View vested amount available for immediate withdrawal
- **Rankings**: See personal ranking compared to other users
- **Mobile-First Design**: Optimized for iPhone and mobile devices

## 🎯 App Workflow

1. **Admin creates tasks** with point values and assigns to users
2. **Users complete tasks** by taking photos with their iPhone
3. **Photos are submitted** for admin verification
4. **Admin reviews submissions** and approves/rejects with notes
5. **Approved tasks** add points to user's total
6. **Daily winner** gets "Child of the Day" award + $10.00
7. **Cash vests** over 6 months from first award
8. **Users can cash out** vested amounts anytime

## 💰 Cash & Vesting System

- **Daily Award**: $10.00 for each "Child of the Day" win
- **Vesting Period**: 6 months from first award
- **Rolling Balance**: Full cash balance grows with each award
- **Early Cash-Out**: Vested amount available immediately
- **Vesting Calculation**: Linear vesting over 180 days

Example: If you earned $100 total and it's been 3 months since your first award, you have $50 vested (available to cash out) and $50 still vesting.

## 🛠 Technology Stack

- **Laravel 12**: PHP framework with latest features
- **Livewire 3**: Reactive frontend components
- **SQLite**: Lightweight database
- **Tailwind CSS**: Mobile-first styling
- **Breeze**: Authentication scaffolding

## 🚀 Getting Started

The app is pre-seeded with test data:

### Admin Account
- **Email**: admin@chores.test
- **Password**: password

### User Accounts
- **Alice Smith**: alice.smith@chores.test / password
- **Bob Johnson**: bob.johnson@chores.test / password
- **Charlie Brown**: charlie.brown@chores.test / password
- **Dana Wilson**: dana.wilson@chores.test / password

### Sample Tasks
- Clean Your Room (15 pts, daily)
- Take Out Trash (10 pts, weekly)
- Load/Unload Dishwasher (8 pts, daily)
- Feed Pets (5 pts, daily)
- Vacuum Living Room (12 pts, weekly)
- Wash Car (25 pts, one-time)
- Organize Garage (35 pts, one-time)
- Weed Garden (20 pts, weekly)

## 📱 Mobile Features

### iPhone Camera Integration
- Native camera access for photo uploads
- Optimized for iOS devices
- 10MB max file size for photos
- Automatic photo compression

### Mobile-First Design
- Touch-friendly interface
- Swipe-friendly navigation
- Responsive grid layouts
- Large tap targets
- Readable typography

## 🔒 Security Features

- **Role-based access**: Admin vs User permissions
- **Photo verification**: All completions require photo proof
- **Admin verification**: Human review of all submissions
- **Secure uploads**: Photos stored securely with proper validation

## 📊 User Interface

### Dashboard
- **Admin**: Task management, verification queue, user stats
- **User**: Personal stats, pending tasks, cash balance, rankings

### Key Screens
- Task List with completion status
- Photo upload for task completion
- Admin verification interface
- User balance and vesting details
- Rankings and leaderboards

## 🎮 How to Use

### For Admins
1. Login with admin account
2. Create tasks in "Manage Tasks"
3. Assign tasks to users
4. Review submissions in "Verify Completions"
5. Award daily winner manually or automatically

### For Users
1. Login with user account
2. View assigned tasks on dashboard
3. Complete tasks by uploading photos
4. Track points and rankings
5. Monitor cash balance and vesting
6. Cash out vested amounts when ready

## 🔧 Development

The app runs on localhost:8000 and includes:
- Complete database schema
- Seeded test data
- Mobile-responsive design
- Photo upload functionality
- Cash balance calculations
- User ranking system

## 🏆 Awards System

The "Child of the Day" award is given to the user with the most approved points each day:
- Automatically calculated based on verified completions
- $10.00 cash award per day won
- Ties are broken by earliest completion time
- Award can be manually triggered by admin
- Only one winner per day

## 💡 Future Enhancements

Potential features for future development:
- Push notifications for new tasks
- Family chat/messaging
- Achievement badges
- Weekly/monthly challenges
- Photo galleries of completed tasks
- Task templates and categories
- Recurring task automation
- Family member profiles
- Export capabilities for tracking

---

🎯 **Start the server and visit http://localhost:8000 to begin using the Chore Tracker!**
