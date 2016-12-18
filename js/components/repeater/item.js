import React from 'react';
import ReactDOM from 'react-dom';
import shortid from 'shortid';

export default class RepeaterItem extends React.Component {

	constructor(props) {
		super(props);
		this._setValue = this._setValue.bind(this);
	}

	_setValue(key, value) {
		this.props.onUserInput(
			this.props.itemIndex,
			key,
			value
		);

	}

	getChildContext() {
		return {
			setValue: this._setValue
		};
	}

	componentWillMount(){
		this.elementId = shortid.generate()
	}
}
RepeaterItem.childContextTypes = {
	setValue: React.PropTypes.object.isRequired
}
