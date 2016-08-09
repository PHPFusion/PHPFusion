<?php
$locale['gallery_0001'] = "Gallery";
$locale['gallery_0002'] = "Add Photo";
$locale['gallery_0003'] = "Edit Photo";
$locale['gallery_0004'] = "Add Album";
$locale['gallery_0005'] = "Edit Album";
$locale['gallery_0006'] = "Gallery Settings";
$locale['gallery_0007'] = "Gallery Submissions";
$locale['gallery_0009'] = "Single Photo Upload";
$locale['gallery_0010'] = "Mass Photo Upload";
$locale['gallery_0011'] = "No Photo Albums defined.";
$locale['gallery_0012'] = "There are no Photo Albums defined. You must at least have one category before you can add any Photos. <a href='%s'>Click here</a> to go to Photo Albums";
$locale['gallery_0013'] = "Photo Actions";
$locale['gallery_0014'] = "Move Photo Up";
$locale['gallery_0015'] = "Move Photo Down";
$locale['gallery_0016'] = "Edit Photo";
$locale['gallery_0017'] = "Delete Photo";
$locale['gallery_0018'] = "Currently displaying %d of %d total album entries";
$locale['gallery_0019'] = "Currently displaying %d of %d total photo entries";
$locale['gallery_0020'] = "Album last updated:";
$locale['gallery_0021'] = "Album visibility:";
$locale['gallery_0022'] = "Gallery Administration";
$locale['gallery_0023'] = "There are total %d albums and %d photos in gallery. Gallery last updated %s";
$locale['gallery_0024'] = "There are no albums defined.";

// Album Form
$locale['album_0001'] = "Album Title:";
$locale['album_0002'] = "Name of Gallery Album";
$locale['album_0003'] = "Album Description:";
$locale['album_0004'] = "Describe the album";
$locale['album_0005'] = "Keywords:";
$locale['album_0006'] = "Hit enter after each keyword";
$locale['album_0007'] = "Visibility:";
$locale['album_0008'] = "Language:";
$locale['album_0009'] = "Album Thumbnail:";
$locale['album_0010'] = "Max. filesize: %s / Allowed filetypes: %s / Max width: %spx, Max. height: %spx";
$locale['album_0011'] = "Album Order";
$locale['album_0012'] = "Save Album";
$locale['album_0013'] = "Photo album updated";
$locale['album_0014'] = "Photo album created";
$locale['album_0015'] = "Please enter an Album Name";
$locale['album_0016'] = "Delete Album Thumbnail";

// Gallery actions & front page (first tab)
$locale['album_0020'] = "Album Actions";
$locale['album_0021'] = "Move Up";
$locale['album_0022'] = "Move Down";
$locale['album_0023'] = "Delete Album";
$locale['album_0024'] = "Edit Album";
$locale['album_0025'] = "Album Moved Up";
$locale['album_0026'] = "Album Moved Down";
$locale['album_0027'] = "There are photos in the current album";
$locale['album_0028'] = "Delete the entire Album";
$locale['album_0029'] = "Move album photos to .. %s";
$locale['album_0030'] = "Album deleted";
$locale['album_0031'] = "Album photos moved to %s";
$locale['album_0032'] = "%d photos in the album deleted";

// Photo Form
$locale['photo_0001'] = "Photo Title:";
$locale['photo_0002'] = "Photo Name";
$locale['photo_0003'] = "Photo Album:";
$locale['photo_0004'] = "Photo Image:";
$locale['photo_0005'] = "Photo Keywords:";
$locale['photo_0006'] = $locale['album_0005']; // i put like this, and no dependencies.
$locale['photo_0007'] = $locale['album_0006'];
$locale['photo_0008'] = "Photo Description";
$locale['photo_0009'] = "Describe the photo";
$locale['photo_0010'] = "Allow Photo Comments?";
$locale['photo_0011'] = "Allow Photo Ratings?";
$locale['photo_0012'] = "Save Photo";
$locale['photo_0013'] = "Photo Order";
$locale['photo_0014'] = "Photo image is required";
$locale['photo_0015'] = "Photo is updated";
$locale['photo_0016'] = "Photo is added";
$locale['photo_0017'] = $locale['album_0010'];
$locale['photo_0018'] = "Delete Photo";
$locale['photo_0019'] = "You can batch upload up to 20 photos here. Click +Add Photo and hold and drag across multiple images to select the images. Click Save Photo to start uploading your photos.";
$locale['photo_0020'] = "Upload Selected Photos";
$locale['photo_0021'] = "%d photos have been added";
$locale['photo_0021a'] = "%d photos was not uploaded due to errors";
$locale['photo_0022'] = "Photo Moved Up";
$locale['photo_0023'] = "Photo Moved Down";
$locale['photo_0024'] = "Photo deleted";
$locale['photo_0025'] = "Purge All Photos";
$locale['photo_0026'] = "<strong>WARNING:</strong> Purge actions will <strong>permanently remove all photos</strong> in this album. Are you sure?";
$locale['photo_0027'] = "Confirm Purge";
$locale['photo_0028'] = "Cancel";


// Submissions form
$locale['gallery_0100'] = "Photo Submissions";
$locale['gallery_0101'] = "Thank you for submitting your Photo";
$locale['gallery_0102'] = "Submit another Photo";
$locale['gallery_0103'] = $locale['photo_0003'];
$locale['gallery_0104'] = $locale['photo_0001'];
$locale['gallery_0105'] = $locale['photo_0005'];
$locale['gallery_0106'] = $locale['album_0006'];
$locale['gallery_0107'] = "Use the following form to submit a Photo. Your submission will be reviewed by an
Administrator. ".fusion_get_settings('sitename')." reserves the right to amend or edit any submission. Photos
should be applicable to the content of this site. Submissions deemed unsuitable will be rejected.";
$locale['gallery_0106'] = $locale['photo_0008'];
$locale['gallery_0109'] = $locale['photo_0004'];
$locale['gallery_0110'] = $locale['photo_0014'];
$locale['gallery_0111'] = "Submit Photo";
$locale['gallery_0112'] = "Sorry, we currently do not accept any photo submissions on this site.";
$locale['gallery_0113'] = "Return to ".fusion_get_settings("sitename");

// Submissions admin
$locale['gallery_0150'] = "There are currently no photo submissions";
$locale['gallery_0151'] = "There are currently %s pending for your review.";
$locale['gallery_0152'] = "Photo submission title for Review";
$locale['gallery_0153'] = "Submission Author";
$locale['gallery_0154'] = "Submission Time";
$locale['gallery_0155'] = "Submission ID";
$locale['gallery_0156'] = "The above photo was submitted by ";
$locale['gallery_0157'] = "Posted by ";
$locale['gallery_0158'] = "Publish Photo";
$locale['gallery_0159'] = "Delete Submission";
$locale['gallery_0160'] = "Photo Submission has been published";
$locale['gallery_0161'] = "Photo Submission deleted";

// Settings
$locale['gallery_0200'] = "Allow photo submissions?";
$locale['gallery_0201'] = "Require photo description?";
$locale['gallery_0202'] = "Thumbs per page:";
$locale['gallery_0203'] = "Thumbnail size:";
$locale['gallery_0204'] = "width x height";
$locale['gallery_0205'] = "Photo size:";
$locale['gallery_0206'] = "Photo max. size:";
$locale['gallery_0207'] = "Photo max. file size:";
$locale['gallery_0208'] = "Album title watermark color:";
$locale['gallery_0209'] = "Album description watermark color:";
$locale['gallery_0210'] = "Photo title watermark color:";
$locale['gallery_0211'] = "Delete existing watermarks";
$locale['gallery_0212'] = "Specify .png image watermark";
$locale['gallery_0213'] =  "Enable text watermark on photos?";
$locale['gallery_0214'] = "Enable photos watermark?";
$locale['gallery_0215'] = "Save generated watermarks?";
$locale['gallery_0216'] = "Save Gallery Settings";

// temporary
// Error Album messages
$locale['635'] = "Gallery Album :";
$locale['636'] = "Uploaded by:";
$locale['637'] = "Date Added:";
$locale['638'] = "Rate";
$locale['639'] = "Comment";
$locale['640'] = "Photo Description:";
$locale['641'] = "Number of Views";
$locale['642'] = "Ratings";
$locale['643'] = "Comments";
$locale['644'] = "Dimensions";
$locale['645'] = "Image Type";
$locale['646'] = "Channels";
$locale['647'] = "Bits";
$locale['648'] = "ISO";
$locale['649'] = "Exposure";
$locale['650'] = "Aperture";
$locale['651'] = "Camera";
$locale['652'] = "Camera Model";
$locale['655'] = "Keywords:";
$locale['660'] = "There are no photos in this album";
$locale['702'] = "Photo uploaded";
$locale['703'] = "Photo updated";


// Photo Gallery Settings
$locale['600'] = "Gallery";
$locale['601'] = "Thumb size:";
$locale['602'] = "Photo size:";
$locale['603'] = "Maximum photo size:";
$locale['604'] = "Width x Height";
$locale['605'] = "Maximum file size (bytes):";
$locale['606'] = "Thumb compression method";
$locale['607'] = "GD1";
$locale['608'] = "GD2";
$locale['609'] = "Thumbs per row:";
$locale['609b'] = "Thumbs per row (Admin):";
$locale['610'] = "Thumbs per page:";
$locale['611'] = "Enable photos watermark?";
$locale['612'] = "Specify PNG watermark";
$locale['613'] = "Enable text description on photos?";
$locale['614'] = "Album title color";
$locale['615'] = "Album description colour";
$locale['616'] = "Photo title colour";
$locale['617'] = "Save generated watermarks?";
$locale['618'] = "Reduces server load; occupies more disk space";
$locale['619'] = "Delete existing watermarks";
$locale['620'] = "Delete existing watermarks?";
$locale['621'] = "Upload Image";