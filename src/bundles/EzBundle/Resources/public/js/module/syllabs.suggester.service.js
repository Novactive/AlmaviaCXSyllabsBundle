import {handleRequestResponse} from '../../../../../../../../../ezsystems/ezplatform-admin-ui-modules/src/modules/common/helpers/request.helper';
import {showErrorNotification} from '../../../../../../../../../ezsystems/ezplatform-admin-ui-modules/src/modules/common/services/notification.service';

export const loadSuggestions = ({body}, callback) => {
  const endpoint = window.Routing.generate('syllabs_process');
  const request = new Request(endpoint, {
    method: 'POST',
    body: JSON.stringify(body),
    mode: 'same-origin',
    credentials: 'same-origin',
    headers: {
      'Content-type': 'application/json'
    }
  });

  fetch(request)
  .then(handleRequestResponse)
  .then(callback)
  .catch(showErrorNotification);
}
