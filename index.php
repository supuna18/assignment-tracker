<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📘 Assignment Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href ="css/style.css" rel= "stylesheet">
  
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="app-title">
                <i class="fas fa-book-open"></i>
                Assignment Tracker
            </h1>
            <button class="dark-mode-toggle" onclick="toggleDarkMode()">
                <i class="fas fa-moon"></i>
            </button>
        </div>

        <div class="main-content">
            <div class="form-section">
                <h2 class="form-title">
                    ➕ Add New Assignment
                </h2>
                <form id="assignmentForm">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required placeholder="e.g., Mathematics">
                    </div>
                    <div class="form-group">
                        <label for="title">Assignment Title</label>
                        <input type="text" id="title" name="title" required placeholder="e.g., Calculus Problem Set">
                    </div>
                    <div class="form-group">
                        <label for="dueDate">Due Date</label>
                        <input type="date" id="dueDate" name="dueDate" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <textarea id="description" name="description" placeholder="Additional notes or requirements..."></textarea>
                    </div>
                    <button type="submit" class="add-btn">
                        ➕ Add Assignment
                    </button>
                </form>
            </div>

            <div class="assignments-section">
                <div class="assignments-header">
                    <h2 class="assignments-title">
                        📋 Your Assignments
                    </h2>
                </div>
                
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>

                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-number" id="totalCount">0</div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" id="completedCount">0</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" id="urgentCount">0</div>
                        <div class="stat-label">Urgent</div>
                    </div>
                </div>

                <div id="assignmentsList">
                    <div class="empty-state">
                        📋
                        <p>No assignments yet. Add your first assignment to get started!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let assignments = JSON.parse(localStorage.getItem('assignments')) || [];
        let isDarkMode = localStorage.getItem('darkMode') === 'true';

        // Initialize dark mode
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            document.querySelector('.dark-mode-toggle').textContent = '☀️';
        }

        function toggleDarkMode() {
            isDarkMode = !isDarkMode;
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);
            
            const toggleBtn = document.querySelector('.dark-mode-toggle');
            toggleBtn.textContent = isDarkMode ? '☀️' : '🌙';
        }

        function saveAssignments() {
            localStorage.setItem('assignments', JSON.stringify(assignments));
        }

        function calculateDaysLeft(dueDate) {
            const today = new Date();
            const due = new Date(dueDate);
            const timeDiff = due.getTime() - today.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            return daysDiff;
        }

        function getAssignmentStatus(assignment) {
            if (assignment.completed) return 'completed';
            const daysLeft = calculateDaysLeft(assignment.dueDate);
            return daysLeft < 3 ? 'urgent' : 'normal';
        }

        function formatDaysLeft(daysLeft, completed) {
            if (completed) return 'Completed';
            if (daysLeft < 0) return `${Math.abs(daysLeft)} days overdue`;
            if (daysLeft === 0) return 'Due today';
            if (daysLeft === 1) return '1 day left';
            return `${daysLeft} days left`;
        }

        function updateStats() {
            const total = assignments.length;
            const completed = assignments.filter(a => a.completed).length;
            const urgent = assignments.filter(a => !a.completed && calculateDaysLeft(a.dueDate) < 3).length;
            
            document.getElementById('totalCount').textContent = total;
            document.getElementById('completedCount').textContent = completed;
            document.getElementById('urgentCount').textContent = urgent;
            
            const progressPercentage = total > 0 ? (completed / total) * 100 : 0;
            document.getElementById('progressFill').style.width = progressPercentage + '%';
        }

        function renderAssignments() {
            const assignmentsList = document.getElementById('assignmentsList');
            
            if (assignments.length === 0) {
                assignmentsList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No assignments yet. Add your first assignment to get started!</p>
                    </div>
                `;
                return;
            }

            // Sort assignments by due date
            const sortedAssignments = [...assignments].sort((a, b) => {
                if (a.completed && !b.completed) return 1;
                if (!a.completed && b.completed) return -1;
                return new Date(a.dueDate) - new Date(b.dueDate);
            });

            assignmentsList.innerHTML = sortedAssignments.map(assignment => {
                const status = getAssignmentStatus(assignment);
                const daysLeft = calculateDaysLeft(assignment.dueDate);
                const daysLeftText = formatDaysLeft(daysLeft, assignment.completed);
                
                return `
                    <div class="assignment-card ${status}" data-id="${assignment.id}">
                        <div class="assignment-header">
                            <div class="assignment-info">
                                <h3>${assignment.title}</h3>
                                <div class="subject">${assignment.subject}</div>
                            </div>
                            <div class="assignment-actions">
                                ${!assignment.completed ? `
                                    <button class="action-btn complete-btn" onclick="toggleComplete(${assignment.id})" title="Mark as completed">
                                        ✅
                                    </button>
                                ` : `
                                    <button class="action-btn complete-btn" onclick="toggleComplete(${assignment.id})" title="Mark as incomplete" style="background: #666;">
                                        ↩️
                                    </button>
                                `}
                                <button class="action-btn delete-btn" onclick="deleteAssignment(${assignment.id})" title="Delete assignment">
                                    🗑️
                                </button>
                            </div>
                        </div>
                        <div class="assignment-details">
                            <div class="due-date">
                                📅 Due: ${new Date(assignment.dueDate).toLocaleDateString()}
                                <span class="days-left ${status}">${daysLeftText}</span>
                            </div>
                            ${assignment.description ? `<div class="description">${assignment.description}</div>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function addAssignment(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const assignment = {
                id: Date.now(),
                subject: formData.get('subject'),
                title: formData.get('title'),
                dueDate: formData.get('dueDate'),
                description: formData.get('description'),
                completed: false,
                createdAt: new Date().toISOString()
            };

            assignments.push(assignment);
            saveAssignments();
            renderAssignments();
            updateStats();
            
            e.target.reset();
        }

        function toggleComplete(id) {
            const assignment = assignments.find(a => a.id === id);
            if (assignment) {
                assignment.completed = !assignment.completed;
                saveAssignments();
                renderAssignments();
                updateStats();
            }
        }

        function deleteAssignment(id) {
            if (confirm('Are you sure you want to delete this assignment?')) {
                assignments = assignments.filter(a => a.id !== id);
                saveAssignments();
                renderAssignments();
                updateStats();
            }
        }

        // Event listeners
        document.getElementById('assignmentForm').addEventListener('submit', addAssignment);

        // Set minimum date to today
        document.getElementById('dueDate').min = new Date().toISOString().split('T')[0];

        // Initialize the app
        renderAssignments();
        updateStats();
    </script>
</body>
</html>