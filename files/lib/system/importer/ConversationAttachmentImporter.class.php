<?php
namespace wcf\system\importer;
use wcf\data\conversation\message\ConversationMessage;
use wcf\data\conversation\message\ConversationMessageEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\util\StringUtil;

/**
 * Imports conversation attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.conversation
 * @subpackage	system.importer
 * @category	Community Framework
 */
class ConversationAttachmentImporter extends AbstractAttachmentImporter {
	/**
	 * Creates a new ConversationAttachmentImporter object.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', 'com.woltlab.wcf.conversation.message');
		$this->objectTypeID = $objectType->objectTypeID;
	}
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['objectID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.conversation.message', $data['objectID']);
		if (!$data['objectID']) return 0;
		
		$attachmentID = parent::import($oldID, $data, $additionalData);
		if ($attachmentID && $attachmentID != $oldID) {
			// fix embedded attachments
			$messageObj = new ConversationMessage($data['objectID']);
			
			if (($newMessage = $this->fixEmbeddedAttachments($messageObj->message, $oldID, $attachmentID)) !== false) {
				$editor = new ConversationMessageEditor($messageObj);
				$editor->update(array(
					'message' => $newMessage
				));
			}
		}
		
		return $attachmentID;
	}
}
