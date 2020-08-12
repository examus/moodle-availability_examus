<script type="text/javascript">
    (function(){
      var str_awaiting_proctoring = <?= json_encode(get_string('fader_awaiting_proctoring', 'availability_examus')) ?>;
      var str_instructions = <?= json_encode(get_string('fader_instructions', 'availability_examus')) ?>;
      //msg queue, inited ASAP, so we don't miss anything
      var examus_q = [];
      var expected_origin = <?= json_encode($origin) ?>;

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
