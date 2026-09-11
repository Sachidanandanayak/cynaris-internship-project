/**
 * ============================================================================
 * Cynaris Solutions - Dynamic To-Do Application
 * Week 2 Day 4: DOM Manipulation, Event Delegation & LocalStorage Persistence
 * ============================================================================
 * 
 * Core Requirements & DOM APIs Demonstrated:
 *  1. querySelector / querySelectorAll: Element targeting & DOM caching
 *  2. createElement: In-memory dynamic node creation
 *  3. appendChild: Hierarchical tree construction
 *  4. removeChild: Safe removal of elements from the DOM tree
 *  5. classList: State styling (classList.add, remove, toggle, contains)
 *  6. Event Delegation: Single listener on parent #todo-list handling child actions
 *  7. localStorage: Persistent serialization & automatic load restoration
 * ============================================================================
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // ------------------------------------------------------------------------
    // 1. DOM Element Cache via querySelector
    // ------------------------------------------------------------------------
    const todoForm = document.querySelector('#todo-form');
    const todoInput = document.querySelector('#todo-input');
    const todoList = document.querySelector('#todo-list');
    const emptyState = document.querySelector('#empty-state');
    const formFeedback = document.querySelector('#form-feedback');
    const todoCount = document.querySelector('#todo-count');
    const statTotal = document.querySelector('#stat-total');
    const statActive = document.querySelector('#stat-active');
    const statCompleted = document.querySelector('#stat-completed');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const clearCompletedBtn = document.querySelector('#clear-completed-btn');

    // Storage Key identifier
    const STORAGE_KEY = 'cynaris_todos_v1';

    // Active filter state: 'all' | 'active' | 'completed'
    let currentFilter = 'all';

    // ------------------------------------------------------------------------
    // 2. LocalStorage Persistence Functions
    // ------------------------------------------------------------------------
    
    /**
     * Serializes current list items from the DOM into localStorage
     */
    const saveTodosToStorage = () => {
        try {
            const items = [];
            const todoElements = todoList.querySelectorAll('.todo-item');
            
            todoElements.forEach((el) => {
                const textEl = el.querySelector('.todo-text');
                const checkbox = el.querySelector('.todo-checkbox');
                if (textEl) {
                    items.push({
                        id: el.dataset.id || String(Date.now()),
                        text: textEl.textContent.trim(),
                        completed: checkbox ? checkbox.checked : el.classList.contains('completed'),
                        createdAt: el.dataset.createdAt || new Date().toISOString()
                    });
                }
            });

            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
            updateMetrics();
        } catch (error) {
            console.error('Failed to save todos to localStorage:', error);
        }
    };

    /**
     * Reads serialized todos from localStorage
     * @returns {Array<{ id: string, text: string, completed: boolean, createdAt: string }>}
     */
    const getStoredTodos = () => {
        try {
            const rawData = localStorage.getItem(STORAGE_KEY);
            return rawData ? JSON.parse(rawData) : [];
        } catch (error) {
            console.error('Failed to parse todos from localStorage:', error);
            return [];
        }
    };

    // ------------------------------------------------------------------------
    // 3. Dynamic DOM Creation (createElement, appendChild, classList)
    // ------------------------------------------------------------------------

    /**
     * Constructs and mounts a new Todo item into the DOM
     * Demonstrates: createElement, appendChild, and classList
     * 
     * @param {Object} todoData - Data object with text, completed, id, createdAt
     * @param {boolean} [prepend=false] - Prepend to top of list if true
     * @returns {HTMLElement} The created <li> element
     */
    const createTodoElement = (todoData, prepend = false) => {
        const { id = String(Date.now()), text, completed = false, createdAt = new Date().toISOString() } = todoData;

        // 1. Create list item container via createElement
        const li = document.createElement('li');
        li.dataset.id = id;
        li.dataset.createdAt = createdAt;
        
        // 2. Manipulate CSS classes using classList
        li.classList.add('todo-item');
        if (completed) {
            li.classList.add('completed');
        }

        // 3. Create Left Main wrapper via createElement
        const mainDiv = document.createElement('div');
        mainDiv.classList.add('todo-main');

        // 4. Create Accessible Checkbox wrapper & input
        const checkboxWrapper = document.createElement('div');
        checkboxWrapper.classList.add('todo-checkbox-wrapper');

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.classList.add('todo-checkbox');
        checkbox.checked = completed;
        checkbox.setAttribute('aria-label', `Mark "${text}" as ${completed ? 'incomplete' : 'complete'}`);

        checkboxWrapper.appendChild(checkbox);

        // 5. Create Task Text element via createElement
        const textSpan = document.createElement('span');
        textSpan.classList.add('todo-text');
        textSpan.textContent = text;
        textSpan.title = 'Click to toggle completion';

        // Append checkbox and text to main wrapper
        mainDiv.appendChild(checkboxWrapper);
        mainDiv.appendChild(textSpan);

        // 6. Create Delete Button via createElement
        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.classList.add('todo-delete-btn');
        deleteBtn.setAttribute('aria-label', `Delete task: "${text}"`);
        deleteBtn.title = 'Delete task';
        deleteBtn.innerHTML = `
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        `;

        // 7. Assemble elements using appendChild
        li.appendChild(mainDiv);
        li.appendChild(deleteBtn);

        // 8. Mount into document list using appendChild or insertBefore
        if (prepend && todoList.firstChild) {
            todoList.insertBefore(li, todoList.firstChild);
        } else {
            todoList.appendChild(li);
        }

        // Apply current active filter visibility
        applyFilterToElement(li, currentFilter);

        return li;
    };

    // ------------------------------------------------------------------------
    // 4. Safe Removal (removeChild)
    // ------------------------------------------------------------------------

    /**
     * Removes an item element safely using removeChild with an exit animation
     * @param {HTMLElement} itemElement - The <li> element to dismount
     */
    const removeTodoItem = (itemElement) => {
        if (!itemElement || !itemElement.parentNode) return;

        // Apply visual exit animation using classList
        itemElement.classList.add('removing');

        // Allow CSS transition to play then invoke removeChild
        setTimeout(() => {
            if (itemElement.parentNode === todoList) {
                // Explicitly demonstrates removeChild API requirement
                todoList.removeChild(itemElement);
                saveTodosToStorage();
                updateMetrics();
            }
        }, 180);
    };

    // ------------------------------------------------------------------------
    // 5. Event Delegation (Attached to #todo-list container)
    // ------------------------------------------------------------------------

    /**
     * Event delegation handler: handles clicks on checkboxes, task text, and delete buttons.
     * Newly added dynamic items work immediately without separate listeners!
     */
    todoList.addEventListener('click', (event) => {
        const target = event.target;

        // Find parent .todo-item
        const todoItem = target.closest('.todo-item');
        if (!todoItem) return;

        // ACTION A: Delete Button Click
        const deleteButton = target.closest('.todo-delete-btn');
        if (deleteButton) {
            event.stopPropagation();
            removeTodoItem(todoItem);
            return;
        }

        // ACTION B: Checkbox Toggle or Text Click (Toggle Completion)
        const isCheckbox = target.classList.contains('todo-checkbox');
        const isText = target.classList.contains('todo-text');

        if (isCheckbox || isText) {
            const checkbox = todoItem.querySelector('.todo-checkbox');
            
            // If user clicked text, toggle checkbox state
            if (isText && checkbox) {
                checkbox.checked = !checkbox.checked;
            }

            const isCompleted = checkbox ? checkbox.checked : !todoItem.classList.contains('completed');

            // Use classList.toggle to update visual completion state
            todoItem.classList.toggle('completed', isCompleted);

            // Update ARIA accessibility
            if (checkbox) {
                const taskText = todoItem.querySelector('.todo-text')?.textContent || 'task';
                checkbox.setAttribute('aria-label', `Mark "${taskText}" as ${isCompleted ? 'incomplete' : 'complete'}`);
            }

            // Sync with active filter and persist
            applyFilterToElement(todoItem, currentFilter);
            saveTodosToStorage();
            updateMetrics();
        }
    });

    // ------------------------------------------------------------------------
    // 6. Adding New Items via Form Submission
    // ------------------------------------------------------------------------
    todoForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const rawValue = todoInput.value;
        const trimmedText = rawValue ? rawValue.trim() : '';

        // Input validation
        if (!trimmedText) {
            formFeedback.textContent = 'Please enter a task description before submitting.';
            todoInput.focus();
            return;
        }

        // Clear feedback message
        formFeedback.textContent = '';

        // Create new item object
        const newTodo = {
            id: 'todo_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
            text: trimmedText,
            completed: false,
            createdAt: new Date().toISOString()
        };

        // Create DOM element and prepend to list
        createTodoElement(newTodo, true);

        // Persist to localStorage
        saveTodosToStorage();

        // Reset input and maintain focus
        todoInput.value = '';
        todoInput.focus();

        updateMetrics();
    });

    // Clear feedback on typing
    todoInput.addEventListener('input', () => {
        if (formFeedback.textContent) {
            formFeedback.textContent = '';
        }
    });

    // ------------------------------------------------------------------------
    // 7. Filtering and Visibility Control (classList)
    // ------------------------------------------------------------------------

    const applyFilterToElement = (el, filter) => {
        const isCompleted = el.classList.contains('completed');

        if (filter === 'all') {
            el.style.display = 'flex';
        } else if (filter === 'active') {
            el.style.display = isCompleted ? 'none' : 'flex';
        } else if (filter === 'completed') {
            el.style.display = isCompleted ? 'flex' : 'none';
        }
    };

    const applyFilter = (filter) => {
        currentFilter = filter;
        const allItems = todoList.querySelectorAll('.todo-item');
        allItems.forEach((el) => applyFilterToElement(el, filter));

        // Update active tab styling with classList
        filterTabs.forEach((tab) => {
            const matches = tab.dataset.filter === filter;
            tab.classList.toggle('active', matches);
            tab.setAttribute('aria-pressed', String(matches));
        });

        checkEmptyState();
    };

    filterTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const selectedFilter = tab.dataset.filter || 'all';
            applyFilter(selectedFilter);
        });
    });

    // ------------------------------------------------------------------------
    // 8. Clear Completed Tasks
    // ------------------------------------------------------------------------
    clearCompletedBtn.addEventListener('click', () => {
        const completedItems = todoList.querySelectorAll('.todo-item.completed');
        if (completedItems.length === 0) return;

        completedItems.forEach((item) => {
            removeTodoItem(item);
        });
    });

    // ------------------------------------------------------------------------
    // 9. Metrics & Empty State Synchronizer
    // ------------------------------------------------------------------------
    const updateMetrics = () => {
        const allItems = todoList.querySelectorAll('.todo-item');
        const completedItems = todoList.querySelectorAll('.todo-item.completed');
        
        const totalCount = allItems.length;
        const completedCount = completedItems.length;
        const activeCount = totalCount - completedCount;

        if (statTotal) statTotal.textContent = String(totalCount);
        if (statActive) statActive.textContent = String(activeCount);
        if (statCompleted) statCompleted.textContent = String(completedCount);
        if (todoCount) {
            todoCount.textContent = `${activeCount} ${activeCount === 1 ? 'item' : 'items'} remaining`;
        }

        checkEmptyState();
    };

    const checkEmptyState = () => {
        const visibleItems = Array.from(todoList.querySelectorAll('.todo-item')).filter(
            (el) => el.style.display !== 'none'
        );

        if (visibleItems.length === 0) {
            emptyState.classList.add('visible');
        } else {
            emptyState.classList.remove('visible');
        }
    };

    // ------------------------------------------------------------------------
    // 10. Automatic Restoration from LocalStorage on Page Load
    // ------------------------------------------------------------------------
    const restoreTodosOnLoad = () => {
        const savedTodos = getStoredTodos();

        // Clear existing children to prevent duplication
        while (todoList.firstChild) {
            todoList.removeChild(todoList.firstChild);
        }

        if (savedTodos.length > 0) {
            // Restore saved items in chronological order
            savedTodos.forEach((todoData) => {
                createTodoElement(todoData, false);
            });
        } else {
            // Seed initial sample tasks if empty for instant interactive demonstration
            const initialDemoTasks = [
                { id: 'task-1', text: 'Configure multi-cloud VPC peering (AWS & GCP)', completed: true, createdAt: new Date().toISOString() },
                { id: 'task-2', text: 'Audit zero-trust IAM policy RBAC permissions', completed: false, createdAt: new Date().toISOString() },
                { id: 'task-3', text: 'Execute automated Canary deployment verification', completed: false, createdAt: new Date().toISOString() }
            ];
            initialDemoTasks.forEach((task) => createTodoElement(task, false));
            saveTodosToStorage();
        }

        updateMetrics();
        console.log(`%c Cynaris Solutions - To-Do App Loaded (${getStoredTodos().length} items in localStorage) `, 'background: #06b6d4; color: #0b0f19; font-weight: bold; padding: 4px 8px; border-radius: 4px;');
    };

    // Initialize
    restoreTodosOnLoad();

    // Keyboard shortcut: Escape clears input
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.activeElement === todoInput) {
            todoInput.value = '';
            formFeedback.textContent = '';
        }
    });
});
