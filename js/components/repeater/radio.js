import React from 'react';
import ReactDOM from 'react-dom';

export default class RadioField extends React.Component {
	constructor(props) {
		super(props);
		this._handleChange = this._handleChange.bind(this);
	}

	_handleChange(e) {

		var el = e.target;

		this.context.setValue(
			this.props.settingName,
			el.value
		)
	}

	render() {

		const className = this.props.horizontal ? 'gravityflow-radio-horizontal' : 'gravityflow-radio-vertical';

		return (<div className={className}><label>
					<input
						type="radio"
						name={this.props.name}
						value={this.props.value}
						checked={this.props.checked}
						onChange={this._handleChange}/>{this.props.label}
				</label>
			</div>);
	}

}
RadioField.contextTypes = {
	setValue: React.PropTypes.object.isRequired
};
