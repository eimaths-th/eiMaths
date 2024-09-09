
 <!-- Load jQuery first -->
 @stack('scripts')

 <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

 <script>
     document.addEventListener('DOMContentLoaded', () => {
         const dropdownButton = document.querySelector('.dropdown-button');
         const dropdownMenu = document.querySelector('.dropdown-menu');
         const manageDropdownButton = document.querySelector('.manage-dropdown-button');
         const manageDropdownMenu = document.querySelector('.manage-dropdown-menu');

         if (dropdownButton && dropdownMenu) {
             dropdownButton.addEventListener('click', () => {
                 dropdownMenu.classList.toggle('hidden');
             });

             document.addEventListener('click', (event) => {
                 if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                     dropdownMenu.classList.add('hidden');
                 }
             });
         }

         if (manageDropdownButton && manageDropdownMenu) {
             manageDropdownButton.addEventListener('click', () => {
                 manageDropdownMenu.classList.toggle('hidden');
             });

             document.addEventListener('click', (event) => {
                 if (!manageDropdownButton.contains(event.target) && !manageDropdownMenu.contains(event
                         .target)) {
                     manageDropdownMenu.classList.add('hidden');
                 }
             });
         }
     });

     function toggleDropdown() {
         const menu = document.querySelector('.sm:hidden');
         if (menu) {
             menu.classList.toggle('hidden');
         }
     }
 </script>
