document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('myList');
    const items = list.getElementsByTagName('li');
  
    for (let i = 0; i < items.length; i++) {
      items[i].style.listStyleType = 'none';
  
      const icon = document.createElement('span');
      icon.className = 'fa-regular fa-circle-dot'; 
  
      items[i].insertBefore(icon, items[i].firstChild);
  
      items[i].style.display = 'flex';
      items[i].style.alignItems = 'center';
      icon.style.marginRight = '8px';
    }
  });
  