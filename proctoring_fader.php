<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();
?>
<script type="text/javascript">
    (function(){
      var str_awaiting_proctoring = <?php echo json_encode(get_string('fader_awaiting_proctoring', 'availability_examus')) ?>;
      var str_instructions = <?php echo json_encode(get_string('fader_instructions', 'availability_examus')) ?>;
      //msg queue, inited ASAP, so we don't miss anything
      var examus_q = [];
      var expected_origin = <?php echo json_encode($origin) ?>;

      console.log(expected_origin);

      window.addEventListener("message", function(e){
        console.log(e.origin, expected_origin);
        if(e.origin == expected_origin){
          examus_q.push(e.data); console.log(e.data);
        }
        check();
      });
      var examusFader;
      window.addEventListener("DOMContentLoaded", function(){
        console.log("loaded");
        examusFader = document.createElement("DIV");
        examusFader.innerHTML = str_awaiting_proctoring + str_instructions;
        examusFader.style="position: fixed; z-index: 1000; font-size: 2em; width: 100%; height: 100%; background: #fff; top: 0; left: 0;text-align: center;display: flex;justify-content: center;align-content: center;flex-direction: column;";
        document.body.appendChild(examusFader);
        if(!check()){
          if(window.parent && window.parent != window){
            window.parent.postMessage('proctoringRequest', expected_origin);
          }
        }
      });
      function check(){
        if(examus_q && examus_q[0]){
          unlock();
          return true;
        }
      }
      function unlock(){ if(examusFader) examusFader.remove(); examusFader = null; }
    })();
</script>
