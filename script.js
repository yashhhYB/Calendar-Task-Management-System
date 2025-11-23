const state = {
    currentDate: new Date(),
    selectedDate: new Date(),
    tasks: [],
    currentView: 'calendar',
    filters: {
        category: ['work', 'personal', 'other'],
        listSearch: '',
        listPriority: '',
        listStatus: '',
        listCategory: ['work', 'personal', 'other']
    }
};

// DOM Elements
const calendarDays = document.getElementById('calendarDays');
const currentMonthYear = document.getElementById('currentMonthYear');
const taskModal = document.getElementById('taskModal');
const taskForm = document.getElementById('taskForm');
const deleteBtn = document.getElementById('deleteBtn');
const selectedDateTitle = document.getElementById('selectedDateTitle');
const selectedDateTaskList = document.getElementById('selectedDateTaskList');
const listResults = document.getElementById('listResults');
const resultCount = document.getElementById('resultCount');

// Initialization
document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();
    updateSelectedDateSidebar();
    fetchTasks();
    setupEventListeners();
});

function setupEventListeners() {
    // Navigation
    document.getElementById('prevMonth')?.addEventListener('click', () => changeMonth(-1));
    document.getElementById('nextMonth')?.addEventListener('click', () => changeMonth(1));

    // List View Filters
    document.getElementById('listSearchInput')?.addEventListener('input', (e) => {
        state.filters.listSearch = e.target.value;
        renderListView();
    });

    document.getElementById('listStatusFilter')?.addEventListener('change', (e) => {
        state.filters.listStatus = e.target.value;
        renderListView();
    });

    // Form
    taskForm?.addEventListener('submit', handleFormSubmit);

    // Close modal on outside click
    taskModal?.addEventListener('click', (e) => {
        if (e.target === taskModal) closeTaskModal();
    });
}

// Global functions for HTML onclick
window.switchView = switchView;
window.goToToday = goToToday;
window.openTaskModal = openTaskModal;
window.closeTaskModal = closeTaskModal;
window.deleteCurrentTask = deleteCurrentTask;
window.toggleFilter = toggleFilter;
window.setListFilter = setListFilter;
window.toggleListCategory = toggleListCategory;
window.toggleStatus = toggleStatus;
window.importCSV = importCSV;

function switchView(view) {
    state.currentView = view;
    document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
    document.getElementById(`${view}View`)?.classList.add('active');
    
    if (view === 'list') {
        renderListView();
    } else {
        renderCalendar();
        renderCalendarTasks();
    }
}

// Calendar View Logic
function toggleFilter(type, value, checked) {
    if (type === 'category') {
        if (checked) {
            if (!state.filters.category.includes(value)) state.filters.category.push(value);
        } else {
            state.filters.category = state.filters.category.filter(c => c !== value);
        }
        renderCalendarTasks();
    }
}

function changeMonth(delta) {
    state.currentDate.setMonth(state.currentDate.getMonth() + delta);
    renderCalendar();
    renderCalendarTasks();
}

function goToToday() {
    state.currentDate = new Date();
    state.selectedDate = new Date();
    renderCalendar();
    renderCalendarTasks();
    updateSelectedDateSidebar();
}

// List View Logic
function setListFilter(type, value) {
    const chips = document.querySelectorAll(`.filter-chip`);
    chips.forEach(chip => {
        if (chip.dataset.value === value) {
            if (state.filters.listPriority === value) {
                state.filters.listPriority = '';
                chip.classList.remove('bg-blue-100', 'text-blue-800', 'border-blue-200');
                chip.classList.add('border-gray-300', 'text-gray-600');
            } else {
                state.filters.listPriority = value;
                chips.forEach(c => {
                    c.classList.remove('bg-blue-100', 'text-blue-800', 'border-blue-200');
                    c.classList.add('border-gray-300', 'text-gray-600');
                });
                chip.classList.remove('border-gray-300', 'text-gray-600');
                chip.classList.add('bg-blue-100', 'text-blue-800', 'border-blue-200');
            }
        }
    });
    renderListView();
}

function toggleListCategory(value) {
    if (state.filters.listCategory.includes(value)) {
        state.filters.listCategory = state.filters.listCategory.filter(c => c !== value);
    } else {
        state.filters.listCategory.push(value);
    }
    renderListView();
}

async function fetchTasks() {
    try {
        const response = await fetch('api.php?action=get_tasks');
        const result = await response.json();
        if (result.success) {
            state.tasks = result.data;
            renderCalendarTasks();
            renderListView();
            updateSelectedDateSidebar();
        }
    } catch (error) {
        console.error('Error fetching tasks:', error);
    }
}

function renderCalendar() {
    const year = state.currentDate.getFullYear();
    const month = state.currentDate.getMonth();
    
    if (currentMonthYear) {
        currentMonthYear.textContent = new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    }

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    if (calendarDays) {
        calendarDays.innerHTML = '';
        
        for (let i = 0; i < firstDay; i++) {
            const cell = document.createElement('div');
            cell.className = 'border-b border-r border-google-border min-h-[100px] bg-white';
            calendarDays.appendChild(cell);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const cell = document.createElement('div');
            cell.className = 'border-b border-r border-google-border min-h-[100px] bg-white p-1 relative group hover:bg-gray-50 transition-colors cursor-pointer';
            
            if (new Date(dateStr).toDateString() === state.selectedDate.toDateString()) {
                cell.classList.add('bg-blue-50');
            }

            cell.onclick = () => {
                state.selectedDate = new Date(dateStr);
                renderCalendar();
                renderCalendarTasks();
                updateSelectedDateSidebar();
            };

            const isToday = new Date().toDateString() === new Date(year, month, day).toDateString();
            
            cell.innerHTML = `
                <div class="text-center mb-1">
                    <span class="date-number text-xs font-medium inline-block w-6 h-6 leading-6 rounded-full ${isToday ? 'bg-google-blue text-white' : 'text-gray-700'}">${day}</span>
                </div>
                <div class="space-y-1 task-container" id="date-${dateStr}"></div>
            `;
            calendarDays.appendChild(cell);
        }
    }
}

function renderCalendarTasks() {
    document.querySelectorAll('.task-container').forEach(el => el.innerHTML = '');
    
    state.tasks.forEach(task => {
        if (!state.filters.category.includes(task.category)) return;
        
        const container = document.getElementById(`date-${task.due_date}`);
        if (container) {
            const taskEl = document.createElement('div');
            let bgClass = 'bg-google-blue';
            if (task.category === 'personal') bgClass = 'bg-green-600';
            if (task.category === 'other') bgClass = 'bg-purple-600';
            
            taskEl.className = `text-xs text-white px-2 py-1 rounded shadow-sm truncate cursor-pointer hover:opacity-90 transition ${bgClass} ${task.status === 'completed' ? 'opacity-60 line-through' : ''}`;
            taskEl.textContent = task.title;
            
            taskEl.onclick = (e) => {
                e.stopPropagation();
                openTaskModal(task);
            };
            container.appendChild(taskEl);
        }
    });
}

function updateSelectedDateSidebar() {
    const dateStr = state.selectedDate.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
    if (selectedDateTitle) selectedDateTitle.textContent = dateStr;

    const isoDate = state.selectedDate.toISOString().split('T')[0];
    const tasksForDate = state.tasks.filter(t => t.due_date === isoDate);

    if (selectedDateTaskList) {
        selectedDateTaskList.innerHTML = '';
        
        if (tasksForDate.length === 0) {
            selectedDateTaskList.innerHTML = '<div class="text-center text-gray-400 mt-10 text-sm">No tasks for this day</div>';
            return;
        }

        tasksForDate.forEach(task => {
            const el = document.createElement('div');
            el.className = 'bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:shadow-md transition cursor-pointer group';
            el.onclick = () => openTaskModal(task);
            
            el.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="mt-1 cursor-pointer text-gray-400 hover:text-google-blue" onclick="event.stopPropagation(); toggleStatus(${task.id})">
                        <span class="material-icons-outlined text-xl">${task.status === 'completed' ? 'check_circle' : 'radio_button_unchecked'}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-800 truncate ${task.status === 'completed' ? 'line-through text-gray-500' : ''}">${task.title}</h4>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">${task.description || ''}</p>
                        <div class="flex gap-2 mt-2">
                            <span class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded uppercase tracking-wide">${task.priority}</span>
                            <span class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded uppercase tracking-wide">${task.category}</span>
                        </div>
                    </div>
                </div>
            `;
            selectedDateTaskList.appendChild(el);
        });
    }
}

function renderListView() {
    const filteredTasks = state.tasks.filter(task => {
        if (state.filters.listSearch && !task.title.toLowerCase().includes(state.filters.listSearch.toLowerCase()) && !task.description.toLowerCase().includes(state.filters.listSearch.toLowerCase())) return false;
        if (state.filters.listPriority && task.priority !== state.filters.listPriority) return false;
        if (state.filters.listStatus && task.status !== state.filters.listStatus) return false;
        if (!state.filters.listCategory.includes(task.category)) return false;
        return true;
    });

    filteredTasks.sort((a, b) => new Date(a.due_date).getTime() - new Date(b.due_date).getTime());

    if (resultCount) resultCount.textContent = `${filteredTasks.length} tasks found`;

    if (listResults) {
        listResults.innerHTML = '';
        
        if (filteredTasks.length === 0) {
            listResults.innerHTML = '<div class="text-center text-gray-500 py-10">No tasks match your filters</div>';
            return;
        }

        filteredTasks.forEach(task => {
            const el = document.createElement('div');
            el.className = 'bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition flex items-center justify-between group';
            
            let priorityColor = 'bg-gray-100 text-gray-600';
            if (task.priority === 'high') priorityColor = 'bg-red-100 text-red-700';
            if (task.priority === 'medium') priorityColor = 'bg-yellow-100 text-yellow-700';
            if (task.priority === 'low') priorityColor = 'bg-green-100 text-green-700';

            let categoryColor = 'bg-blue-100 text-blue-700';
            if (task.category === 'personal') categoryColor = 'bg-green-100 text-green-700';
            if (task.category === 'other') categoryColor = 'bg-purple-100 text-purple-700';

            el.innerHTML = `
                <div class="flex items-center gap-4 flex-1">
                    <button onclick="toggleStatus(${task.id})" class="text-gray-400 hover:text-google-blue transition">
                        <span class="material-icons-outlined text-2xl">${task.status === 'completed' ? 'check_circle' : 'radio_button_unchecked'}</span>
                    </button>
                    <div>
                        <h3 class="text-base font-medium text-gray-900 ${task.status === 'completed' ? 'line-through text-gray-500' : ''}">${task.title}</h3>
                        <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                            <span class="material-icons-outlined text-sm">event</span>
                            <span>${new Date(task.due_date).toLocaleDateString()}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-2 py-1 rounded text-xs font-medium ${priorityColor}">${task.priority}</span>
                    <span class="px-2 py-1 rounded text-xs font-medium ${categoryColor}">${task.category}</span>
                    <button onclick="openTaskModal(${JSON.stringify(task).replace(/"/g, '&quot;')})" class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                        <span class="material-icons-outlined">edit</span>
                    </button>
                </div>
            `;
            listResults.appendChild(el);
        });
    }
}

function openTaskModal(task = null) {
    const taskId = document.getElementById('taskId');
    taskForm.reset();
    deleteBtn.classList.add('hidden');

    if (task && task.id) {
        taskId.value = task.id;
        document.getElementById('taskTitle').value = task.title;
        document.getElementById('taskDescription').value = task.description;
        document.getElementById('taskDueDate').value = task.due_date;
        document.getElementById('taskPriority').value = task.priority;
        document.getElementById('taskCategory').value = task.category;
        document.getElementById('taskStatus').value = task.status;
        deleteBtn.classList.remove('hidden');
    } else {
        taskId.value = '';
        if (task && task.due_date) {
            document.getElementById('taskDueDate').value = task.due_date;
        } else {
            document.getElementById('taskDueDate').value = state.selectedDate.toISOString().split('T')[0];
        }
    }
    taskModal.classList.remove('hidden');
}

function closeTaskModal() {
    taskModal.classList.add('hidden');
}

function deleteCurrentTask() {
    const id = document.getElementById('taskId').value;
    if (id) deleteTask(id);
}

async function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(taskForm);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('api.php?action=save_task', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            closeTaskModal();
            fetchTasks();
        } else {
            alert(result.error);
        }
    } catch (error) {
        console.error('Error saving task:', error);
    }
}

async function deleteTask(id) {
    if (!confirm('Are you sure?')) return;
    
    try {
        const response = await fetch('api.php?action=delete_task', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        if ((await response.json()).success) {
            closeTaskModal();
            fetchTasks();
        }
    } catch (error) {
        console.error('Error deleting task:', error);
    }
}

async function toggleStatus(id) {
    try {
        const response = await fetch('api.php?action=toggle_status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        if ((await response.json()).success) {
            fetchTasks();
        }
    } catch (error) {
        console.error('Error updating status:', error);
    }
}

async function importCSV(input) {
    if (!input.files || !input.files[0]) return;
    
    const formData = new FormData();
    formData.append('csv_file', input.files[0]);
    
    try {
        const response = await fetch('api.php?action=import_csv', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            fetchTasks();
        } else {
            alert(result.error);
        }
    } catch (error) {
        console.error('Error importing CSV:', error);
        alert('Failed to import CSV');
    }
    input.value = '';
}
