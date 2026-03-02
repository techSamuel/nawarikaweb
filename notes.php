<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Hind Siliguri', sans-serif; }
        #login-view { display: flex; }
        #notes-app-container { display: none; }
        
        /* Updated selection style to orange and white */
        .note-list-item.active { 
            background-color: #F97316; /* orange-500 */
            color: white;
        }
        .note-list-item.active h3, .note-list-item.active p {
            color: white;
        }
        
        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

        /* Styles for the editor and highlighter */
        .editor-container {
            position: relative;
            flex-grow: 1;
            overflow: hidden;
        }
        .editor-shared {
            width: 100%;
            height: 100%;
            padding: 1.5rem; /* p-6 */
            font-size: 1.125rem; /* text-lg */
            line-height: 32px;
            white-space: pre-wrap;
            word-wrap: break-word;
            border: none;
            outline: none;
            resize: none;
            background: transparent;
        }
        #note-body-editor {
            
            caret-color: black;
            background-image:
                linear-gradient(to bottom, transparent 31px, #E5E7EB 32px);
            background-size: 100% 32px;
        }
        #highlighter {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            overflow: hidden;
        }
        #highlighter mark {
            background-color: #fef08a; /* yellow-200 */
            color: transparent; /* Makes the highlight a block of color behind the caret */
        }
    </style>
</head>
<body class="bg-gray-100 h-screen overflow-hidden">

    <div id="login-view" class="min-h-screen w-full items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
            <h2 class="text-2xl font-bold text-center mb-6">Notes</h2>
            <form id="login-form">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                </div>
                <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600">Login</button>
                <p id="login-error" class="text-red-500 text-sm mt-2 text-center"></p>
            </form>
        </div>
    </div>

    <div id="notes-app-container" class="h-screen w-full flex">
        <aside class="w-1/3 lg:w-1/4 h-full bg-gray-200 border-r border-gray-300 flex flex-col">
            <div class="p-2 border-b border-gray-300 flex justify-between items-center">
                <h2 class="text-lg font-bold px-2 hidden md:block">Notes</h2>
                <div>
                    <button id="new-note-btn" title="New Note" class="p-2 rounded-md hover:bg-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>
                    <button id="logout-btn" title="Logout" class="p-2 rounded-md hover:bg-gray-300">
                       <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </div>
            </div>
            <div class="p-2 border-b border-gray-300">
                <input id="search-input" type="search" placeholder="Search notes..." class="w-full px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm">
            </div>
            <div id="notes-list" class="flex-grow overflow-y-auto">
                </div>
        </aside>

        <main id="main-content" class="w-2/3 lg:w-3/4 h-full flex flex-col bg-white">
            <div id="no-note-selected" class="flex-grow flex items-center justify-center">
                <p class="text-gray-500 text-lg">Select a note to view or create a new one.</p>
            </div>
            
            <div id="note-editor" class="hidden flex-grow flex-col">
                <div class="p-2 border-b border-gray-200 flex justify-end items-center space-x-2">
                     <button id="lock-note-btn" title="Lock Note" class="p-2 text-gray-500 hover:text-blue-600 rounded-md hover:bg-gray-100">
                        <svg id="unlock-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v3m-6 2h12a2 2 0 002-2v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7a2 2 0 002 2z"></path></svg>
                        <svg id="lock-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                    </button>
                     <button id="delete-note-btn" title="Delete Note" class="p-2 text-gray-500 hover:text-red-600 rounded-md hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                <div class="editor-container">
                    <div id="highlighter" class="editor-shared"></div>
                    <textarea id="note-body-editor" class="editor-shared" spellcheck="false"></textarea>
                </div>
            </div>
        </main>
    </div>

    <div id="password-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 h-full w-full items-center justify-center hidden">
        <div class="relative mx-auto p-5 border w-full max-w-sm shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Authentication Required</h3>
            <div class="mt-2">
                <p class="text-sm text-gray-500">This action requires you to re-enter your password.</p>
            </div>
            <form id="password-form" class="mt-4">
                <input type="password" id="modal-password-input" placeholder="Password" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                <p id="password-modal-error" class="text-red-500 text-sm mt-2 h-4"></p>
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="button" id="cancel-password-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600">Confirm</button>
                </div>
            </form>
        </div>
    </div>


    <script type="module">
        const firebaseConfig = {
          apiKey: "AIzaSyDw1o2kY5kl5oGNhgDP5rRxfvtK20vz0f0",
          authDomain: "nawarika-73f50.firebaseapp.com",
          projectId: "nawarika-73f50",
          storageBucket: "nawarika-73f50.firebasestorage.app",
          messagingSenderId: "357981390108",
          appId: "1:357981390108:web:ad931ea157c1021314a6d9",
          measurementId: "G-9JRWT461NQ"
        };

        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
        import { getAuth, signInWithEmailAndPassword, onAuthStateChanged, signOut, EmailAuthProvider, reauthenticateWithCredential } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-auth.js";
        import { getFirestore, collection, onSnapshot, doc, updateDoc, query, orderBy, addDoc, deleteDoc, serverTimestamp, getDoc } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-firestore.js";
        
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const db = getFirestore(app);

        // --- DOM Elements ---
        const loginView = document.getElementById('login-view');
        const notesAppContainer = document.getElementById('notes-app-container');
        const loginForm = document.getElementById('login-form');
        const loginError = document.getElementById('login-error');
        const logoutBtn = document.getElementById('logout-btn');
        const newNoteBtn = document.getElementById('new-note-btn');
        const deleteNoteBtn = document.getElementById('delete-note-btn');
        const lockNoteBtn = document.getElementById('lock-note-btn');
        const unlockIcon = document.getElementById('unlock-icon');
        const lockIcon = document.getElementById('lock-icon');
        const notesList = document.getElementById('notes-list');
        const noNoteSelectedView = document.getElementById('no-note-selected');
        const noteEditorView = document.getElementById('note-editor');
        const noteBodyEditor = document.getElementById('note-body-editor');
        const searchInput = document.getElementById('search-input');
        const highlighter = document.getElementById('highlighter');
        // Password Modal Elements
        const passwordModal = document.getElementById('password-modal');
        const passwordForm = document.getElementById('password-form');
        const modalPasswordInput = document.getElementById('modal-password-input');
        const passwordModalError = document.getElementById('password-modal-error');
        const cancelPasswordBtn = document.getElementById('cancel-password-btn');

        // --- App State ---
        let currentActiveNoteId = null;
        let unsubscribeNotes;
        let localNotesCache = [];
        let passwordProtectedAction = null; // Can be 'unlock' or 'delete'

        const debounce = (func, delay) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        };

        onAuthStateChanged(auth, user => {
            if (user) {
                loginView.style.display = 'none';
                notesAppContainer.style.display = 'flex';
                loadNotes();
            } else {
                loginView.style.display = 'flex';
                notesAppContainer.style.display = 'none';
                if (unsubscribeNotes) unsubscribeNotes();
                notesList.innerHTML = '';
                localNotesCache = [];
                showEditor(false);
            }
        });

        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            loginError.textContent = '';
            signInWithEmailAndPassword(auth, loginForm.email.value, loginForm.password.value)
                .catch(error => { loginError.textContent = "Invalid email or password."; });
        });
        logoutBtn.addEventListener('click', () => signOut(auth));

        function showEditor(show) {
            noNoteSelectedView.style.display = show ? 'none' : 'flex';
            noteEditorView.style.display = show ? 'flex' : 'none';
        }

        function renderNotesList(notes) {
            notesList.innerHTML = '';
            if (notes.length === 0) {
                 notesList.innerHTML = `<p class="text-center text-gray-500 p-4 text-sm">No notes found.</p>`;
                 return;
            }
            const lockSVG = `<svg class="w-4 h-4 inline-block mr-1 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>`;

            notes.forEach(note => {
                const lines = note.content.split('\n');
                const title = lines[0] || 'New Note';
                const snippet = lines[1] || 'No additional text';

                const listItem = document.createElement('div');
                listItem.className = `note-list-item p-4 border-b border-gray-300 cursor-pointer hover:bg-gray-300 ${note.id === currentActiveNoteId ? 'active' : ''}`;
                listItem.dataset.id = note.id;
                listItem.innerHTML = `
                    <h3 class="font-bold truncate text-gray-800">${note.isLocked ? lockSVG : ''}${title}</h3>
                    <p class="text-sm text-gray-600 truncate">${snippet}</p>
                `;
                notesList.appendChild(listItem);
            });
        }
        
        async function displayNoteContent(noteId) {
            if (!noteId) {
                showEditor(false);
                return;
            }
            const noteDocRef = doc(db, "notes", noteId);
            const noteSnap = await getDoc(noteDocRef);

            if (noteSnap.exists()) {
                const noteData = noteSnap.data();
                noteBodyEditor.value = noteData.content;
                updateLockIcon(noteData.isLocked);
                updateHighlighting();
                showEditor(true);
                noteBodyEditor.focus();
            } else {
                currentActiveNoteId = null;
                showEditor(false);
            }
        }
        
        function loadNotes() {
            const q = query(collection(db, "notes"), orderBy("timestamp", "desc"));
            if (unsubscribeNotes) unsubscribeNotes();
            
            unsubscribeNotes = onSnapshot(q, (snapshot) => {
                localNotesCache = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
                const searchTerm = searchInput.value.toLowerCase();
                const filteredNotes = localNotesCache.filter(note => searchTerm ? note.content.toLowerCase().includes(searchTerm) : true);
                renderNotesList(filteredNotes);
                
                if (currentActiveNoteId && !localNotesCache.some(note => note.id === currentActiveNoteId)) {
                    currentActiveNoteId = null;
                    showEditor(false);
                } else if (currentActiveNoteId) {
                    // Refresh lock icon state in editor if it changed
                    const currentNote = localNotesCache.find(n => n.id === currentActiveNoteId);
                    if (currentNote) {
                        updateLockIcon(currentNote.isLocked);
                    }
                }
            });
        }

        const handleAutoSave = debounce(async () => {
            if (!currentActiveNoteId) return;
            try {
                // NOTE: Removed timestamp update to prevent reordering on edit
                await updateDoc(doc(db, "notes", currentActiveNoteId), {
                    content: noteBodyEditor.value
                });
            } catch (error) {
                console.error("Error auto-saving note: ", error);
            }
        }, 500);

        function updateHighlighting() {
            const content = noteBodyEditor.value;
            const searchTerm = searchInput.value;
            
            if (!searchTerm) {
                highlighter.innerHTML = '';
                return;
            }

            const escapedContent = content.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
            const regex = new RegExp(searchTerm, 'gi');
            const highlightedContent = escapedContent.replace(regex, (match) => `<mark>${match}</mark>`);
            
            highlighter.innerHTML = highlightedContent + '\n';
        }

        newNoteBtn.addEventListener('click', async () => {
            try {
                const newNoteRef = await addDoc(collection(db, "notes"), {
                    content: "New Note\n",
                    isLocked: false,
                    timestamp: serverTimestamp()
                });
                currentActiveNoteId = newNoteRef.id;
                searchInput.value = '';
                await displayNoteContent(currentActiveNoteId);
                // The onSnapshot listener will re-render the list
            } catch (error) {
                console.error("Error creating new note: ", error);
            }
        });

        notesList.addEventListener('click', (e) => {
            const listItem = e.target.closest('.note-list-item');
            if (listItem) {
                const noteId = listItem.dataset.id;
                if (noteId !== currentActiveNoteId) {
                    currentActiveNoteId = noteId;
                    displayNoteContent(noteId);
                    document.querySelectorAll('.note-list-item').forEach(item => {
                        item.classList.toggle('active', item.dataset.id === noteId);
                    });
                }
            }
        });
        
        noteBodyEditor.addEventListener('input', () => {
            updateHighlighting();
            handleAutoSave();
        });

        noteBodyEditor.addEventListener('scroll', () => {
            highlighter.scrollTop = noteBodyEditor.scrollTop;
            highlighter.scrollLeft = noteBodyEditor.scrollLeft;
        });

        deleteNoteBtn.addEventListener('click', async () => {
            if (!currentActiveNoteId) return;

            const note = localNotesCache.find(n => n.id === currentActiveNoteId);
            if (note && note.isLocked) {
                passwordProtectedAction = 'delete';
                showPasswordModal();
                return;
            }

            if (confirm('Are you sure you want to delete this note?')) {
                try {
                    await performDelete();
                } catch (error) {
                    console.error("Error deleting note: ", error);
                }
            }
        });
        
        async function performDelete() {
             const noteToDeleteId = currentActiveNoteId;
             currentActiveNoteId = null;
             showEditor(false);
             await deleteDoc(doc(db, "notes", noteToDeleteId));
        }

        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();
            const filteredNotes = localNotesCache.filter(note => note.content.toLowerCase().includes(searchTerm));
            renderNotesList(filteredNotes);
            updateHighlighting();
        });

        // --- Locking Logic ---
        function updateLockIcon(isLocked) {
            if(isLocked) {
                lockIcon.classList.remove('hidden');
                unlockIcon.classList.add('hidden');
                lockNoteBtn.title = "Unlock Note";
            } else {
                lockIcon.classList.add('hidden');
                unlockIcon.classList.remove('hidden');
                lockNoteBtn.title = "Lock Note";
            }
        }

        lockNoteBtn.addEventListener('click', async () => {
            if (!currentActiveNoteId) return;
            const note = localNotesCache.find(n => n.id === currentActiveNoteId);
            if (!note) return;

            if (note.isLocked) {
                // We need to unlock, which requires a password
                passwordProtectedAction = 'unlock';
                showPasswordModal();
            } else {
                // We can lock without a password
                await updateDoc(doc(db, "notes", currentActiveNoteId), { isLocked: true });
            }
        });

        function showPasswordModal() {
            modalPasswordInput.value = '';
            passwordModalError.textContent = '';
            passwordModal.style.display = 'flex';
            modalPasswordInput.focus();
        }

        function hidePasswordModal() {
            passwordModal.style.display = 'none';
            passwordProtectedAction = null;
        }

        cancelPasswordBtn.addEventListener('click', hidePasswordModal);

        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = modalPasswordInput.value;
            if (!password) return;

            const user = auth.currentUser;
            if (!user) return;
            
            passwordModalError.textContent = '';
            const credential = EmailAuthProvider.credential(user.email, password);

            try {
                await reauthenticateWithCredential(user, credential);
                
                // Re-authentication successful, perform the action
                if (passwordProtectedAction === 'unlock') {
                    await updateDoc(doc(db, "notes", currentActiveNoteId), { isLocked: false });
                } else if (passwordProtectedAction === 'delete') {
                    await performDelete();
                }
                hidePasswordModal();
            } catch (error) {
                passwordModalError.textContent = "Incorrect password. Please try again.";
            }
        });
    </script>
</body>
</html>