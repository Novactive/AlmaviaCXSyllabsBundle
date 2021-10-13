class Suggester {
  constructor(container, restInfo, sourceFields) {
    this.title = Translator.trans(/*@Desc("Select location")*/
        'suggester.title', {}, 'syllabs');
    this.container = container;
    this.restInfo = restInfo;
    this.sourceFields = sourceFields;
  }

  /**
   * @returns {{id: number}}
   */
  getSourceValues() {
    const values = {
      id: 123,
    };
    for (const [sourceFieldsType, sourceFields] of this.sourceFields) {
      const typeValues = [];
      for (const sourceField of sourceFields) {
        typeValues.push(sourceField.value);
      }
      values[sourceFieldsType] = typeValues;
    }
    return values;
  }

  open(config, {onConfirm, onCancel}) {
    const sourceValues = this.getSourceValues();
    ReactDOM.render(
        React.createElement(
            eZ.modules.SyllabsSuggester,
            Object.assign(
                {
                  onConfirm,
                  onCancel,
                  title: this.title,
                  restInfo: this.restInfo,
                  sourceValues,
                },
                config,
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
  constructor(container, fieldIdentifier, suggester, languageCode) {
    this.inputs = new Map();
    this.container = container;
    this.annotationTypeConfigs = [];

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

    this.suggester = suggester;
    this.languageCode = languageCode;
  }

  addAnnotationTypeConfig(annotationType, config) {
    this.annotationTypeConfigs[annotationType] = config;
  }

  addTag(tag) {
    this.updateInput('ids', tag.id);
    this.updateInput('locales', tag.locale);
    this.updateInput('parent_ids', tag.parent_id);
    this.updateInput('keywords', tag.name);
    const eZTagsPlugin = jQuery('.tagssuggest', this.container).data('EzTags');

    eZTagsPlugin.add({
      id: tag.id,
      name: tag.name,
      locale: tag.locale,
      parent_id: tag.parent_id
    })
  }

  updateInput(inputName, inputValue) {
    /**
     * @type {HTMLInputElement}
     */
    const input = this.inputs.get(inputName);
    const currentValue = input.value != '' ? input.value.split(TagField.inputSeparator) : [];
    currentValue.push(inputValue);
    input.value = currentValue.join(TagField.inputSeparator);
  }

  addTags(suggestions) {
    for (const suggestion of suggestions) {
      this.addTag({
        id: suggestion.id,
        locale: this.languageCode,
        name: suggestion.keywords[this.languageCode],
        parent_id: suggestion.parentTagId,
      });
    }
  }

  openSuggester(event) {
    event.preventDefault();
    event.stopPropagation();

    const onConfirm = (suggestions) => {
      jQuery('.tagssuggest', this.container).EzTags('initialize');

      const jstreeEl = jQuery('.tags-tree', this.container)
      const addTags = this.addTags.bind(this)
      if(jstreeEl) {
        jstreeEl.on('refresh.jstree', function (e, data) {
          addTags(suggestions)
        }.bind(this));
      }else {
        addTags(suggestions)
      }

      this.suggester.close();
    };

    const onCancel = () => this.suggester.close();
    const config = {
      annotationTypeConfigs: this.annotationTypeConfigs,
      languageCode: this.languageCode,
    };
    this.suggester.open(config, {onConfirm, onCancel});
  }
}

TagField.inputNames = [
  'ids',
  'locales',
  'parent_ids',
  'keywords',
];
TagField.inputSeparator = '|#';

(function(global, doc, eZ, React, ReactDOM, Translator) {
  const syllabsConfig = global.eZ.adminUiConfig.syllabs;
  const contentTypeIdentifier = doc.querySelector(
      'input[name="ezrepoforms_content_edit[contentTypeIdentifier]"]').value;
  const languageCode = doc.querySelector(
      'input[name="ezrepoforms_content_edit[languageCode]"]').value;
  const contentTypeConfig = syllabsConfig.contentTypes[contentTypeIdentifier];
  if (typeof contentTypeConfig === 'undefined') {
    return;
  }

  const sourceFields = new Map();
  for (const sourceFieldType in contentTypeConfig.sourceFields) {
    const fields = sourceFields.has(sourceFieldType) ? sourceFields.get(
        sourceFieldType) : [];
    for (const sourceFieldsIdentifier of contentTypeConfig.sourceFields[sourceFieldType]) {
      const sourceFieldEl = document.querySelector(`[name="ezrepoforms_content_edit[fieldsData][${sourceFieldsIdentifier}][value]"]`);
      if (sourceFieldEl) {
        fields.push(sourceFieldEl);
      }
    }

    sourceFields.set(sourceFieldType, fields);
  }

  const suggesterContainer = doc.getElementById('react-udw');
  const token = doc.querySelector('meta[name="CSRF-Token"]').content;
  const siteaccess = doc.querySelector('meta[name="SiteAccess"]').content;
  const suggester = new Suggester(suggesterContainer, {token, siteaccess},
      sourceFields);

  const tagFields = new Map();
  for (key in contentTypeConfig.targetFields) {
    const targetFieldType = contentTypeConfig.targetFields[key].type;
    const fieldIdentifier = contentTypeConfig.targetFields[key].fieldIdentfier;
    let tagField = tagFields.has(fieldIdentifier) ? tagFields.get(
        fieldIdentifier) : null;
    if (!tagField) {
      const targetFieldEl = document.querySelector(`input[name^="ezrepoforms_content_edit[fieldsData][${fieldIdentifier}]"]`);
      if (!targetFieldEl) {
        continue;
      }
      tagField = new TagField(
          targetFieldEl.closest('.ez-field-edit'),
          fieldIdentifier,
          suggester,
          languageCode,
      );
      tagFields.set(fieldIdentifier, tagField);
    }

    tagField.addAnnotationTypeConfig(targetFieldType, {
      parentTagId: contentTypeConfig.targetFields[key].parentTagId,
      subtype: contentTypeConfig.targetFields[key].subtype,
    });
  }

})(window, document, window.eZ, window.React, window.ReactDOM,
    window.Translator);
