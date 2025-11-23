<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Calendar Clone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #dadce0;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #bdc1c6;
        }

        .view-section {
            display: none;
        }

        .view-section.active {
            display: flex;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'google-blue': '#1a73e8',
                        'google-blue-hover': '#1967d2',
                        'google-gray': '#5f6368',
                        'google-gray-bg': '#f1f3f4',
                        'google-border': '#dadce0',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-white text-gray-800 h-screen flex flex-col overflow-hidden">

    <!-- Header -->
    <header
        class="h-16 px-4 border-b border-google-border flex items-center justify-between flex-shrink-0 bg-white z-20">
        <div class="flex items-center gap-3 min-w-[240px]">
            <button class="p-2 rounded-full hover:bg-gray-100 text-google-gray">
                <span class="material-icons-outlined">menu</span>
            </button>
            <div class="flex items-center gap-2">
                <img src="https://ssl.gstatic.com/calendar/images/dynamiclogo_2020q4/calendar_31_2x.png" alt="Logo"
                    class="w-10 h-10">
                <span class="text-xl text-gray-600 font-normal">Calendar</span>
            </div>
        </div>

        <div class="flex items-center gap-4 flex-1 max-w-3xl justify-center">
            <button onclick="goToToday()"
                class="px-4 py-2 border border-google-border rounded hover:bg-gray-50 text-sm font-medium text-gray-700">Today</button>
            <div class="flex items-center gap-1">
                <button id="prevMonth" class="p-1.5 rounded-full hover:bg-gray-100 text-gray-600">
                    <span class="material-icons-outlined text-lg">chevron_left</span>
                </button>
                <button id="nextMonth" class="p-1.5 rounded-full hover:bg-gray-100 text-gray-600">
                    <span class="material-icons-outlined text-lg">chevron_right</span>
                </button>
            </div>
            <h2 id="currentMonthYear" class="text-xl text-gray-700 font-normal min-w-[150px] text-center">September 2023
            </h2>
        </div>

        <div class="flex items-center gap-3 min-w-[240px] justify-end">
            <div class="relative">
                <select id="viewSwitcher" onchange="switchView(this.value)"
                    class="appearance-none bg-white border border-google-border hover:bg-gray-50 px-4 py-2 pr-8 rounded text-sm font-medium text-gray-700 focus:outline-none cursor-pointer">
                    <option value="calendar">Month</option>
                    <option value="list">Schedule (List)</option>
                </select>
                <span
                    class="material-icons-outlined absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none text-lg">arrow_drop_down</span>
            </div>
            <div
                class="w-8 h-8 bg-purple-600 rounded-full text-white flex items-center justify-center text-sm font-medium">
                U
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <div class="flex flex-1 overflow-hidden relative">

        <!-- VIEW 1: CALENDAR MODE -->
        <div id="calendarView" class="view-section active w-full h-full">
            <!-- Left Sidebar (Mini Cal & Nav) -->
            <aside class="w-64 flex-shrink-0 flex flex-col p-4 border-r border-google-border hidden md:flex">
                <button onclick="openTaskModal()"
                    class="w-36 h-12 bg-white rounded-full shadow hover:shadow-lg border border-transparent transition-all flex items-center gap-3 px-4 mb-6 text-google-blue-hover">
                    <img src="https://www.gstatic.com/images/icons/material/colored_icons/2x/create_32dp.png"
                        class="w-8 h-8" alt="Create">
                    <span class="font-medium text-gray-600">Create</span>
                </button>

                <!-- My Calendars Filter -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2 px-2">
                        <span class="text-sm font-medium text-gray-700">My Calendars</span>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                            <input type="checkbox" checked
                                class="form-checkbox text-google-blue rounded focus:ring-0 border-gray-400"
                                onchange="toggleFilter('category', 'work', this.checked)">
                            <span class="text-sm text-gray-700">Work</span>
                        </label>
                        <label class="flex items-center gap-3 px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                            <input type="checkbox" checked
                                class="form-checkbox text-green-600 rounded focus:ring-0 border-gray-400"
                                onchange="toggleFilter('category', 'personal', this.checked)">
                            <span class="text-sm text-gray-700">Personal</span>
                        </label>
                        <label class="flex items-center gap-3 px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                            <input type="checkbox" checked
                                class="form-checkbox text-purple-600 rounded focus:ring-0 border-gray-400"
                                onchange="toggleFilter('category', 'other', this.checked)">
                            <span class="text-sm text-gray-700">Other</span>
                        </label>
                    </div>
                </div>

                <!-- Export/Import -->
                <div class="mt-auto border-t border-google-border pt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2 px-2">Data</h3>
                    <div class="space-y-1">
                        <a href="api.php?action=export_csv"
                            class="flex items-center gap-3 px-2 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-700">
                            <span class="material-icons-outlined text-gray-500">download</span>
                            <span class="text-sm">Export CSV</span>
                        </a>
                        <button onclick="document.getElementById('csvInput').click()"
                            class="flex items-center gap-3 px-2 py-2 hover:bg-gray-100 rounded cursor-pointer text-gray-700 w-full text-left">
                            <span class="material-icons-outlined text-gray-500">upload</span>
                            <span class="text-sm">Import CSV</span>
                        </button>
                        <input type="file" id="csvInput" accept=".csv" class="hidden" onchange="importCSV(this)">
                    </div>
                </div>
            </aside>

            <!-- Center: Calendar Grid -->
            <main class="flex-1 flex flex-col bg-white relative">
                <div class="grid grid-cols-7 border-b border-google-border">
                    <div class="py-2 text-center text-xs font-medium text-gray-500 uppercase">Sun</div>
                    <div class="py-2 text-center text-xs font-medium text-gray-500 uppercase">Mon</div>
                    <div class="py-2 text-center text-xs font-medium text-gray-500 uppercase">Tue</div>
                    <div class="py-2 text-center text-xs font-medium text-gray-500 uppercase">Wed</div>
                    <div class="py-2 text-center text-xs font-medium text-gray-500 uppercase">Thu</div>
                    <div class="py-2 text-center text-xs font-medium text-gray-500 uppercase">Fri</div>
                    <div class="py-2 text-center text-xs font-medium text-gray-500 uppercase">Sat</div>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <div class="calendar-grid min-h-full border-b border-google-border" id="calendarDays">
                        <!-- Days injected here -->
                    </div>
                </div>
            </main>

            <!-- Right Sidebar: Selected Date Tasks -->
            <aside class="w-80 flex-shrink-0 bg-white border-l border-google-border flex flex-col">
                <div class="p-4 border-b border-google-border flex justify-between items-center">
                    <h3 class="font-medium text-gray-700" id="selectedDateTitle">Today</h3>
                    <button onclick="openTaskModal()" class="text-google-blue hover:bg-blue-50 p-1 rounded">
                        <span class="material-icons-outlined text-xl">add</span>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-3" id="selectedDateTaskList">
                    <!-- Selected date tasks injected here -->
                    <div class="text-center text-gray-500 mt-10">Select a date to view tasks</div>
                </div>
            </aside>
        </div>

        <!-- VIEW 2: LIST MODE -->
        <div id="listView" class="view-section w-full h-full bg-gray-50">
            <!-- Left Sidebar: Filters -->
            <aside class="w-72 flex-shrink-0 bg-white border-r border-google-border flex flex-col p-6">
                <h2 class="text-lg font-medium text-gray-800 mb-6">Search & Filters</h2>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <div class="relative">
                            <span
                                class="material-icons-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
                            <input type="text" id="listSearchInput" placeholder="Search tasks..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-google-blue focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="setListFilter('priority', 'high')"
                                class="filter-chip px-3 py-1 rounded-full text-sm border border-gray-300 hover:bg-gray-50 text-gray-600"
                                data-value="high">High</button>
                            <button onclick="setListFilter('priority', 'medium')"
                                class="filter-chip px-3 py-1 rounded-full text-sm border border-gray-300 hover:bg-gray-50 text-gray-600"
                                data-value="medium">Medium</button>
                            <button onclick="setListFilter('priority', 'low')"
                                class="filter-chip px-3 py-1 rounded-full text-sm border border-gray-300 hover:bg-gray-50 text-gray-600"
                                data-value="low">Low</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="text-google-blue focus:ring-google-blue rounded"
                                    onchange="toggleListCategory('work')" checked>
                                <span class="text-sm text-gray-600">Work</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="text-green-600 focus:ring-green-600 rounded"
                                    onchange="toggleListCategory('personal')" checked>
                                <span class="text-sm text-gray-600">Personal</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="text-purple-600 focus:ring-purple-600 rounded"
                                    onchange="toggleListCategory('other')" checked>
                                <span class="text-sm text-gray-600">Other</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="listStatusFilter"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-600 focus:ring-2 focus:ring-google-blue focus:border-transparent">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
            </aside>

            <!-- Right Content: Task List -->
            <main class="flex-1 overflow-y-auto p-8">
                <div class="max-w-3xl mx-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-normal text-gray-800">Task Results</h1>
                        <span class="text-sm text-gray-500" id="resultCount">0 tasks found</span>
                    </div>
                    <div id="listResults" class="space-y-4">
                        <!-- List items injected here -->
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Task Modal (Same as before) -->
    <div id="taskModal"
        class="fixed inset-0 bg-black bg-opacity-40 hidden z-50 flex items-center justify-center transition-opacity">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-[448px] mx-4 overflow-hidden">
            <div class="bg-gray-100 px-4 py-2 flex justify-between items-center handle cursor-move">
                <span class="material-icons-outlined text-gray-500 cursor-pointer"
                    onclick="closeTaskModal()">close</span>
            </div>
            <form id="taskForm" class="p-6 pt-2">
                <input type="hidden" id="taskId" name="id">
                <input type="text" id="taskTitle" name="title" required placeholder="Add title"
                    class="w-full text-2xl text-gray-800 border-0 border-b-2 border-gray-200 focus:border-google-blue focus:ring-0 px-0 py-2 mb-6 placeholder-gray-400 transition-colors">

                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <span class="material-icons-outlined text-gray-500 mt-1">schedule</span>
                        <div class="flex-1">
                            <input type="date" id="taskDueDate" name="due_date" required
                                class="w-full border-none bg-gray-50 rounded px-3 py-2 text-sm text-gray-700 focus:ring-0 cursor-pointer hover:bg-gray-100 transition">
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="material-icons-outlined text-gray-500 mt-1">notes</span>
                        <textarea id="taskDescription" name="description" rows="3" placeholder="Add description"
                            class="w-full border-none bg-gray-50 rounded px-3 py-2 text-sm text-gray-700 focus:ring-0 resize-none placeholder-gray-500 hover:bg-gray-100 transition"></textarea>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="material-icons-outlined text-gray-500 mt-1">flag</span>
                        <select id="taskPriority" name="priority"
                            class="w-full border-none bg-gray-50 rounded px-3 py-2 text-sm text-gray-700 focus:ring-0 cursor-pointer hover:bg-gray-100 transition">
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                        </select>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="material-icons-outlined text-gray-500 mt-1">label</span>
                        <select id="taskCategory" name="category"
                            class="w-full border-none bg-gray-50 rounded px-3 py-2 text-sm text-gray-700 focus:ring-0 cursor-pointer hover:bg-gray-100 transition">
                            <option value="work">Work</option>
                            <option value="personal">Personal</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="flex items-start gap-4">
                        <span class="material-icons-outlined text-gray-500 mt-1">check_circle</span>
                        <select id="taskStatus" name="status"
                            class="w-full border-none bg-gray-50 rounded px-3 py-2 text-sm text-gray-700 focus:ring-0 cursor-pointer hover:bg-gray-100 transition">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-8">
                    <button type="button" id="deleteBtn" onclick="deleteCurrentTask()"
                        class="hidden px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded transition">Delete</button>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-google-blue rounded hover:bg-google-blue-hover shadow-sm transition">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>