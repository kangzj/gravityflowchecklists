import React from 'react';
import ReactDOM from 'react-dom';
import update from 'immutability-helper';
import Sortable from 'react-sortablejs';


export default class Repeater extends React.Component {

	constructor(props) {
		super(props);
		this.state = {draggingIndex: null};
		this.state.items = props.value;
	}

	_handleUserInput( index, key, value ) {

		var newValue = [];
		var keyValuePair = [];
		keyValuePair[key] = value;
		newValue[index] = {$merge :keyValuePair};

		var items = this._getItems();

		var newValues = update(items, newValue );

		this._setValues( newValues );

	}

	_setValues( newItems ) {
		if ( this.props.stateful ) {
			this.setState({ items: newItems}, this.props.onChange( this.props.settingName, newItems ) );
		} else {
			this.context.setValue( this.props.settingName, newItems );
		}
	}

	_addItem( index ) {

		const newItem = this.props.defaultValues();

		var newItems;
		var items = this._getItems();
		if ( items.length > 0 ) {
			newItems = items.slice();
			newItems.splice(index + 1, 0, newItem);
		} else {
			newItems = [newItem];
		}

		this._setValues( newItems )
	}

	_getItems() {
		return this.props.stateful ? this.state.items : this.props.value
	}

	_onChangeOrder( order, sortable ) {
		var lookup = [], newValues = [], items, newVal;

		items = this._getItems();

		for (var i = 0, len = order.length; i < len; i++) {
			lookup[items[i].id] = items[i]
		}
		for (var n = 0, orderLen = order.length; n < orderLen; n++) {
			newVal = lookup[order[n]];
			newValues.push(newVal)
		}

		this._setValues( newValues );

	}

	_deleteItem( index ) {
		const sure = this.props.strings.areYouSure ? this.props.strings.areYouSure : 'Are you sure?';
		if ( confirm( sure ) ) {
			var items = this._getItems();
			var newItems = update(items, {$splice: [[index, 1]]});
			this._setValues(newItems)
		}
	}

	render() {
		var itemNodes;

		var items = this._getItems();

		const itemName = this.props.settingName ? this.props.settingName : 'top';
		if ( items.length === 0 ) {
			const noItems = this.props.strings.noItems ? this.props.strings.noItems : 'No items.';
			const addOne = this.props.strings.addOne ? this.props.strings.addOne : 'Add one';
			itemNodes = <div>{noItems} <a onClick={this._addItem.bind( this, 0 )}>{addOne}</a></div>
		} else {
			itemNodes = items.map((item, index) => {
				var el = React.cloneElement(this.props.children, {
					key: item.id,
					item: item,
					itemIndex: index,
					onUserInput: this._handleUserInput.bind(this),
					onChange: this._handleUserInput.bind(this, index)
				});
				var handle = items.length > 1 ? <i className="fa fa-sort handle"/> : '';

				const minItems = this.props.minItems? this.props.minItems : 0;

				const removeButton = items.length > minItems ? <i onClick={this._deleteItem.bind(this, index)} className="gficon-subtract"/> : '' ;

				return (
					<div className={ "gravityflow-repeater-item " + itemName} data-id={item.id} key={item.id}>
							<div className="gravityflow-handle">{handle}</div>
							<div className="gravityflow-item-container">
								{el}
							</div>
						<div className="gravityflow-buttons-container">
							<i onClick={this._addItem.bind( this, index)} className="gficon-add"/>
							{removeButton}
						</div>

						<br style={{clear: 'left'}} />
					</div>
				)
			});
		}

		const label = this.props.label ? (<div>
												<label>{this.props.label}</label>
											</div>) : '';

		return (<div className="gravityflow-setting" key={itemName}>
			{label}
			<Sortable
				options={{
							animation: 150,
							handle: ".gravityflow-handle"
						}}
				onChange={this._onChangeOrder.bind(this)}
			>
				{itemNodes}
			</Sortable>
		</div>)
	}
}

Repeater.defaultProps = {stateful: true};
Repeater.contextTypes = {
	setValue: React.PropTypes.object.isRequired
};
