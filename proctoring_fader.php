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

const storageKey = 'examus-client-origin';
const urlParam   = 'examus-client-origin';

const expectedData = 'proctoringReady_n6EY';

/*
 * Origin of sender, that is of the proctoring application.
 * We cache it in `sessionStorage`, so that if it occasionally disappears from the url,
 * then we've got a previously known value.
 */

const fromStorage = ()  => sessionStorage.get(storageKey);
const toStorage   = (x) => sessionStorage.set(storageKey, x);

const fromUrl = () =>
  new URL(location.href)
  .searchParams
  .get(urlParam);

/* We prefer the value stored in sessionStorage,
 * to resist against spoofing of the query param. */

/* Read value from url only when there is no value stored yet. */
if (!fromStorage()){
  toStorage(fromUrl());
}

const expectedOrigin = fromStorage();

if (!expectedOrigin) {
  console.error(TAG, 'missing `expectedOrigin`');
}

/**
 * Promise, which resolves when got a message proving the page is being proctored.
 * TODO postpone the effect
 */
const proved = new Promise(resolve => {
  const f = e => {
    console.debug(TAG, 'got some message', e.origin, expectedOrigin);

    if (e.origin === expectedOrigin &&
        e.data === expectedData
    ) {
      resolve();
      console.debug(TAG, 'got proved message', e.data);
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

window.addEventListener("DOMContentLoaded", () => {
  document.body.appendChild(createFader());

  proved.then(() => el.remove());

  /* Most of the time this action is meaningless,
   * at the same time it's always harmless. */
  window.parent.postMessage('proctoringRequest', expectedOrigin);
});

})();
</script>
