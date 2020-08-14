<script type="text/javascript">
/**
 * Hide quiz questions unless it's being proctored.
 *
 * Firstly, hide questions with an overlay element.
 * Then send request to the parent window,
 * and wait for the answer.
 *
 * When got a proper answer, then reveal the quiz content.
 *
 * We expect Examus to work only on fresh browsers,
 * so we use modern javascript here, without any regret or fear.
 * Even if some old browser breaks parsing or executing this,
 * no other scripts will be affected.
 */
(function(){

const strAwaitingProctoring = <?= json_encode(get_string('fader_awaiting_proctoring', 'availability_examus')) ?>;
const strInstructions = <?= json_encode(get_string('fader_instructions', 'availability_examus')) ?>;
const faderHTML = strAwaitingProctoring + strInstructions;

const {sessionStorage, location} = window;

const TAG = 'proctoring fader';

const expectedData = (x) => 'proctoringReady_n6EY';

/**
 * Promise, which resolves when got a message proving the page is being proctored.
 */
const waitForProof = () => new Promise(resolve => {
  const f = e => {
    console.debug(TAG, 'got some message', e.data);

    if (expectedData(e.data)) {
      resolve();
      console.debug(TAG, 'got proving message', e.data);
      window.removeEventListener('message', f);
    }
  }

  window.addEventListener("message", f);
});

/**
 * Prepare the element to cover quiz contents.
 */
const createFader = () => {
  const x = document.createElement("div");

  x.innerHTML = faderHTML;

  const style = {
    position: 'fixed',
    zIndex: 1000,
    fontSize: '2em',
    width: '100%',
    height: '100%',
    background: '#fff',
    top: 0,
    left: 0,
    textAlign: 'center',
    display: 'flex',
    justifyContent: 'center',
    alignContent: 'center',
    flexDirection: 'column',
  };

  Object.assign(x.style, style);

  return x;
};

/**
 * Run.
 */

/* Prepare to catch the message early. */
const proved = waitForProof();

window.addEventListener("DOMContentLoaded", () => {
  const fader = createFader();
  document.body.appendChild(fader);

  proved.then(() => fader.remove());
});

})();
</script>
