/**
 * External dependencies
 */
import $ from 'jquery';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { Fragment, useState } from '@wordpress/element';

function App() {
	const [ isUpgrading, setIsUpgrading ] = useState( false );
	const [ showConfirmation, setShowConfirmation ] = useState( false );
	const [ updateCompleted, setUpdateCompleted ] = useState( false );

	const doUpgrade = () => {
		setIsUpgrading( true );

		$.ajax( {
			url: wcsnUpgrader.ajaxurl,
			method: 'post',
			dataType: 'json',
			data: {
				action: 'wcsn_do_upgrade',
				_wpnonce: wcsnUpgrader.nonce,
			},
		} )
			.done( () => {
				setUpdateCompleted( true );
			} )
			.fail( ( jqXHR ) => {
				if ( jqXHR.responseJSON === -1 ) {
					return alert(
						__( 'Unauthorized operation', 'wc-serial-numbers' )
					);
				}

				jqXHR.responseJSON?.data && alert( jqXHR.responseJSON?.data );
			} )
			.always( () => {
				setIsUpgrading( false );
			} );
	};

	const updateMsg = (
		<Fragment>
			<h3>{ __( 'WooCommerce Serial Numbers', 'wc-serial-numbers' ) }</h3>
			{ ! showConfirmation ? (
				<p>
					{ __(
						'We need to update your install to the latest version.',
						'wc-serial-numbers'
					) }
				</p>
			) : (
				<p className="error-message">
					{ __(
						'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?',
						'wc-serial-numbers'
					) }
				</p>
			) }
			<p>
				{ ! isUpgrading && ! showConfirmation ? (
					<Button
						isPrimary
						onClick={ () => setShowConfirmation( true ) }
					>
						{ __( 'Update', 'wc-serial-numbers' ) }
					</Button>
				) : (
					<Fragment>
						<Button
							isPrimary
							disabled={ isUpgrading }
							isBusy={ isUpgrading }
							onClick={ doUpgrade }
						>
							{ isUpgrading
								? __(
										'Upgrading, please wait…',
										'wc-serial-numbers'
								  )
								: __( 'Yes, Update Now', 'wc-serial-numbers' ) }
						</Button>
						&nbsp;
						<Button
							isSecondary
							disabled={ isUpgrading }
							onClick={ () => setShowConfirmation( false ) }
						>
							{ __( 'No, Cancel It', 'wc-serial-numbers' ) }
						</Button>
					</Fragment>
				) }
			</p>
		</Fragment>
	);

	const updateCompletedMsg = (
		<Fragment>
			<h3>
				{ __(
					'WooCommerce Serial Numbers Updated Successfully!',
					'wc-serial-numbers'
				) }
			</h3>
			<p>
				{ __(
					'All data updated sucessfully. Thank you for using WooCommerce Serial Numbers',
					'wc-serial-numbers'
				) }
			</p>
			<p>
				<Button isPrimary onClick={ () => window.location.reload() }>
					{ __( 'Close', 'wc-serial-numbers' ) }
				</Button>
			</p>
		</Fragment>
	);

	return (
		<div className="notice notice-info">
			{ updateCompleted ? updateCompletedMsg : updateMsg }
		</div>
	);
}

export default App;
