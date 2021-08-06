class Suggester {
  constructor(container, restInfo, sourceFields) {
    this.title = Translator.trans(/*@Desc("Select location")*/
        'add_location.title', {}, 'universal_discovery_widget');
    this.container = container
    this.restInfo = restInfo
    this.sourceFields = sourceFields
  }

  getSourceValues() {
    const values = {}
    for (const [sourceFieldsType, sourceFields] of this.sourceFields) {
      const typeValues = []
      for (const sourceField of sourceFields) {
        typeValues.push(sourceField.value)
      }
      values[sourceFieldsType] = typeValues
    }
    return values
  }
  open({onConfirm, onCancel}) {
    const sourceValues = this.getSourceValues()
    ReactDOM.render(
        React.createElement(
            eZ.modules.SyllabsSuggester,
            Object.assign(
                {
                  onConfirm,
                  onCancel,
                  title: this.title,
                  restInfo: this.restInfo,
                }
            ),
        ),
        this.container,
    );
  }

  close() {
    ReactDOM.unmountComponentAtNode(this.container);
  }
}

class TagField {
  constructor(container, fieldIdentifier, suggester) {
    this.inputs = new Map();
    this.annotationTypes = [];

    for (const inputName of TagField.inputNames) {
      const inputFullName = `ezrepoforms_content_edit[fieldsData][${fieldIdentifier}][value][${inputName}]`;
      const input = container.querySelector(`input[name="${inputFullName}"]`);
      this.inputs.set(inputName, input);
    }

    const suggestBtn = document.createElement('button');
    suggestBtn.setAttribute('type', 'button');
    suggestBtn.classList.add('btn', 'btn-primary');
    suggestBtn.textContent = 'Suggestions';
    suggestBtn.addEventListener('click', this.openSuggester.bind(this), false);

    const label = container.querySelector('label.ez-field-edit__label');
    label.parentNode.append(suggestBtn);

    this.suggester = suggester
  }

  addAnnotationType(annotationType) {
    this.annotationTypes.push(annotationType)
  }

  addTag(tag) {
    this.updateInput('tagids', tag.id);
    this.updateInput('taglocales', tag.locale);
    this.updateInput('tagpids', tag.parent_id);
    this.updateInput('tagnames', tag.name);
  }

  updateInput(inputName, inputValue) {
    /**
     * @type {HTMLInputElement}
     */
    const input = this.inputs.get(inputName);
    const currentValue = input.value.explode(TagField.inputSeparator);
    currentValue.push(inputValue);
    input.value = currentValue.join(TagField.inputSeparator);
  }

  openSuggester(event) {
    event.preventDefault();
    event.stopPropagation();

    const onConfirm = (tags) => {

    };
    const onCancel = () => this.suggester.close();
    this.suggester.open({onConfirm, onCancel})
  }
}
TagField.inputNames = [
  'tagids',
  'taglocales',
  'tagpids',
  'tagnames',
];
TagField.inputSeparator = '|#';

(function(global, doc, eZ, React, ReactDOM, Translator) {
  const syllabsConfig = global.eZ.adminUiConfig.syllabs;
  const contentTypeIdentifier = doc.querySelector('input[name="ezrepoforms_content_edit[contentTypeIdentifier]"]').value;
  const contentTypeConfig = syllabsConfig.contentTypes[contentTypeIdentifier]
  if(typeof contentTypeConfig === 'undefined') {
    return
  }

  const sourceFields = new Map()
  for(const sourceFieldType in contentTypeConfig.sourceFields) {
    const fields = sourceFields.has(sourceFieldType) ? sourceFields.get(sourceFieldType) : []
    for (const sourceFieldsIdentifier of contentTypeConfig.sourceFields[sourceFieldType]) {
      const sourceFieldEl = document.querySelector(`[name="ezrepoforms_content_edit[fieldsData][${sourceFieldsIdentifier}][value]"]`)
      if(sourceFieldEl) {
        fields.push(sourceFieldEl)
      }
    }

    sourceFields.set(sourceFieldType, fields)
  }

  const suggesterContainer = doc.getElementById('react-udw');
  const token = doc.querySelector('meta[name="CSRF-Token"]').content;
  const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
  const suggester = new Suggester(suggesterContainer, {token, siteaccess}, sourceFields)



  const tagFields = new Map()
  for(const targetFieldType in contentTypeConfig.targetFields) {
    const fieldIdentifier = contentTypeConfig.targetFields[targetFieldType].fieldIdentfier
    let tagField = tagFields.has(fieldIdentifier) ? tagFields.get(fieldIdentifier) : null
    if(!tagField) {
      const targetFieldEl = document.querySelector(`input[name^="ezrepoforms_content_edit[fieldsData][${fieldIdentifier}]"]`)
      if(!targetFieldEl) {
        continue
      }
      tagField = new TagField(
          targetFieldEl.closest('.ez-field-edit'),
          fieldIdentifier,
          suggester
      )
      tagFields.set(fieldIdentifier, tagField)
    }

    tagField.addAnnotationType(targetFieldType)
  }

})(window, document, window.eZ, window.React, window.ReactDOM,
    window.Translator);
