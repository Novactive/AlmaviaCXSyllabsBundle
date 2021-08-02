import React, { Component } from 'react';
import PropTypes from 'prop-types';
import UniversalDiscoveryModule
  from '../../../../../../../../../ezsystems/ezplatform-admin-ui-modules/src/modules/universal-discovery/universal.discovery.module';

export default class SyllabsSuggesterModule extends Component {
  render() {
    const componentClassName = 'm-ud';
    const containerClassName = `${componentClassName}`;
    const cancelBtnLabel = Translator.trans(/*@Desc("Cancel")*/ 'cancel.label', {}, 'universal_discovery_widget');

    return (
        <div className="m-ud__wrapper">
          <div className={containerClassName}>
            <h1 className="m-ud__title">{this.props.title}</h1>
            <div className="m-ud__content-wrapper">
              <div className="m-ud__content">
                test
              </div>
              <div className="m-ud__actions">
                <div className="m-ud__btns">
                  <button type="button" className="m-ud__action m-ud__action--cancel" onClick={this.props.onCancel}>
                    {cancelBtnLabel}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
    );
  }
}

eZ.addConfig('modules.SyllabsSuggester', SyllabsSuggesterModule);

SyllabsSuggesterModule.propTypes = {
  title: PropTypes.string,
  onConfirm: PropTypes.func.isRequired,
  onCancel: PropTypes.func.isRequired,
  restInfo: PropTypes.shape({
    token: PropTypes.string.isRequired,
    siteaccess: PropTypes.string.isRequired,
  }).isRequired,
}

SyllabsSuggesterModule.defaultProps = {
  title: 'Suggestions'
};
