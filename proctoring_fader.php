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

var str_awaiting_proctoring = <?= json_encode(get_string('fader_awaiting_proctoring', 'availability_examus')) ?>;
var str_instructions = <?= json_encode(get_string('fader_instructions', 'availability_examus')) ?>;
let fader_html = str_awaiting_proctoring + str_instructions

let {sessionStorage, location} = window

let TAG = 'proctoring fader'

let key = 'examus-client-origin'

let expected_data = 'proctoringReady_n6EY'

/*
 * Origin of sender, that is of the proctoring application.
 * We cache it in `sessionStorage`, so that if it occasionally disappears from the url,
 * then we've got a previously known value.
 */

let from_storage = () => sessionStorage.get(key)
let to_storage = x => sessionStorage.set(key, x)
let from_url = () =>
  new URL(location.href)
  .searchParams
  .get('examus-client-origin')

/* We prefer the value stored in sessionStorage,
 * to resist against spoofing of the query param. */

/* Read value from url only when there is no value stored yet. */
if (!from_storage()) to_storage(from_url())

let expected_origin = from_storage()

if (!expected_origin) {
  console.error(TAG, 'missing `expected_origin`')
}

/**
 * Promise, which resolves when got a message proving the page is being proctored.
 * TODO postpone the effect
 */
let proved = new Promise(resolve => {
  let f = e => {
    console.debug(TAG, 'got some message', e.origin, expected_origin)

    if (e.origin === expected_origin &&
        e.data === expected_data
    ) {
      resolve()
      console.debug(TAG, 'got proved message', e.data)
      window.removeEventListener('message', f)
    }
  }

  window.addEventListener("message", f)
})

/**
 * Prepare the element to cover quiz contents.
 */
const examus_fader = () => {
  const x = document.createElement("div");

  x.innerHTML = fader_html;

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

window.addEventListener("DOMContentLoaded", function() {
  let el = examus_fader()

  document.body.appendChild(el)

  proved.then(() => el.remove())

  /* Most of the time this action is meaningless,
   * at the same time it's always harmless. */
  window.parent.postMessage('proctoringRequest', expected_origin);
});

})();
</script>
