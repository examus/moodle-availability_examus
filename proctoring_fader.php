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

/**
 * Availability plugin for integration with Examus proctoring system.
 *
 * @package    availability_examus
 * @copyright  2019-2020 Maksim Burnin <maksim.burnin@gmail.com>
 * @copyright  based on work by 2017 Max Pomazuev
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
?>
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

const strAwaitingProctoring = <?php echo json_encode(get_string('fader_awaiting_proctoring', 'availability_examus')) ?>;
const strInstructions = <?php echo json_encode(get_string('fader_instructions', 'availability_examus')) ?>;
const faderHTML = strAwaitingProctoring + strInstructions;

const {sessionStorage, location} = window;

const TAG = 'proctoring fader';
const expectedData = 'proctoringReady_n6EY';

/**
 * Promise, which resolves when got a message proving the page is being proctored.
 */
const waitForProof = () => new Promise(resolve => {
  const messageHandler = e => {
    console.debug(TAG, 'got some message', e.data);

    if (expectedData === e.data) {
      resolve();
      console.debug(TAG, 'got proving message', e.data);
      window.removeEventListener('message', messageHandler);
    }
  }

  window.addEventListener("message", messageHandler);
});

/**
 * Prepare the element to cover quiz contents.
 */
const createFader = () => {
  const fader = document.createElement("div");

  fader.innerHTML = faderHTML;

  Object.assign(fader.style, {
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
  });

  return fader;
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
