
    function toggle(btnID, eIDs) {
        // Feed the list of ids as a selector
        var theRows = document.querySelectorAll(eIDs);
        // Get the button that triggered this
        var theButton = document.getElementById(btnID);
        // If the button is not expanded...
        if (theButton.getAttribute("aria-expanded") == "false") {
          // Loop through the rows and show them
          for (var i = 0; i < theRows.length; i++) {
            theRows[i].classList.add("shown");
            theRows[i].classList.remove("hidden");
          }
          // Now set the button to expanded
          theButton.setAttribute("aria-expanded", "true");
        // Otherwise button is not expanded...
        } else {
          // Loop through the rows and hide them
          for (var i = 0; i < theRows.length; i++) {
            theRows[i].classList.add("hidden");
            theRows[i].classList.remove("shown");
          }
          // Now set the button to collapsed
          theButton.setAttribute("aria-expanded", "false");
        }
      }


      function QuickFinder(tablename) {
        var input, filter, found, table, tr, td, i, j;
      input = document.getElementById("QInput");
      filter = input.value.toUpperCase();
        table = document.getElementById(tablename);
        tr = table.getElementsByTagName("tr");
        for (i = 1; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td");
            for (j = 1; j < td.length; j++) {
             
                if ((td[j].innerHTML.toUpperCase().indexOf(filter) > -1)) {
               
                    found = true;
                }
            }
            if (found) {
                tr[i].classList.remove("hidden");
                found = false;
            } else {
              tr[i].classList.add("hidden");

            }
        }
      
    
      
      }
    
      function ClearFilter(tablename) {
      
        document.getElementById("QInput").value = "";
        QuickFinder(tablename);
    }


    function toggleRows(btnID, eIDs) {
      // Feed the list of ids as a selector
      var theRows = document.getElementsByClassName(eIDs);
      // Get the button that triggered this
      var theButton = document.getElementById(btnID);
      // If the button is not expanded...
      if (theButton.getAttribute("aria-expanded") == "false") {
        // Loop through the rows and show them
        for (var i = 0; i < theRows.length; i++) {
          theRows[i].classList.add("shown");
          theRows[i].classList.remove("hidden");
          
        }
        theButton.innerText = "-";
        // Now set the button to expanded
        theButton.setAttribute("aria-expanded", "true");
      // Otherwise button is not expanded...
      } else {
        // Loop through the rows and hide them
        for (var i = 0; i < theRows.length; i++) {
          theRows[i].classList.add("hidden");
          theRows[i].classList.remove("shown");
        }
        theButton.innerText = "+";

        // Now set the button to collapsed
        theButton.setAttribute("aria-expanded", "false");
      }
    }

function toggleAllRows() {
    const buttons = document.querySelectorAll('button');
    for (let i = 0; i < buttons.length; i++) {
      if (buttons[i].id!="tglAllRows" && buttons[i].id!="ClearFilterBtn") {
buttons[i].click();
}

if (buttons[i].id=="tglAllRows") {
  if(buttons[i].innerText == "-") { 
    buttons[i].innerText = "+";
  } else { buttons[i].innerText = "-";}
}

};
}