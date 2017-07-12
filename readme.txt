=== Job Board by BestWebSoft ===
Contributors: bestwebsoft
Donate link: https://bestwebsoft.com/donate/
Tags: add job offer, job board, job board plugin, apply for a job, job, cv, manage vacancies, search by salary, job manager, job offer, job offer categories, job offer list
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 1.1.5
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Create your personal job board and listing WordPress website. Search jobs, submit CV/resumes, choose candidates.

== Description ==

Job Board is a simple plugin which allows to create your personal job WordPress website. Add and manage unlimited job posts, categories and employers. Allow users to register, search jobs, and submit their resume/CV online.

Connect job seekers with their future employers today!

https://www.youtube.com/watch?v=ox2rMnwxfHs

== Features ==

* Add and manage an unlimited number of:
	* Jobs
	* Categories
	* Employment
* Use shortcode to add:
	* Job board
	* Registration form
* Customize your jobs additional info:
	* Requirements
	* Location
	* Organization
	* Salary
	* Expiry date
* Choose employment type:
	* Freelance
	* Full time
	* Internship
	* Part time
	* Temporary
	* Custom
		* Name
		* Slug
		* Parent
		* Description
* Choose currency:
	* From the list
	* Custom
* Choose salary per:
	* Hour
	* Day
	* Week
	* Month
	* Year
* Choose job featured image position:
	* Left
	* Right
* Change location field in the frontend sorting form
* Customize text after CV sending
* Set the job offers default expiry time
* Set daily archiving time
* Job candidates can customize their account:
	* Upload resume/CV file
	* Set the job search category
* Search and view a list with job offers
* Add custom code via plugin settings page
* Compatible with latest WordPress version
* Incredibly simple settings for fast setup without modifying code
* Detailed step-by-step documentation and videos

If you have a feature suggestion or idea you'd like to see in the plugin, we'd love to hear about it! [Suggest a Feature](https://support.bestwebsoft.com/hc/en-us/requests/new)

== Documentation & Videos ==

* [[Doc] Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)
* [[Video] Installation Instruction](https://www.youtube.com/watch?v=-5mDdQmDuIc)

= Help & Support =

Visit our Help Center if you have any questions, our friendly Support Team is happy to help — <https://support.bestwebsoft.com/>

== Translation ==

* Russian (ru_RU)
* Ukrainian (uk)

Some of these translations are not complete. We are constantly adding new features which should be translated. If you would like to create your own language pack or update the existing one, you can send [the text of PO and MO files](https://codex.wordpress.org/Translating_WordPress) to [BestWebSoft](https://support.bestwebsoft.com/hc/en-us/requests/new) and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO [files Poedit](https://www.poedit.net/download.php).

== Recommended Plugins ==

* [Updater](https://bestwebsoft.com/products/wordpress/plugins/updater/?k=c9514c3366ba95825f1470bfc8d75f4f) - Automatically check and update WordPress website core with all installed plugins and themes to the latest versions.
* [Sender](https://bestwebsoft.com/products/wordpress/plugins/sender/?k=ccd218c6d916f9735e3de54ff210e4fe) - Send bulk email messages to WordPress users. Custom templates, advanced settings and detailed reports.
* [Custom Search](https://bestwebsoft.com/products/wordpress/plugins/custom-search/?k=0320eef03b72c22f7448ab163f612a6d) - Add custom post types to WordPress website search results.

== Installation ==

1. Upload the `job-board` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin using the 'Plugins' menu in your WordPress admin panel.
3. You can adjust the necessary settings using your WordPress admin panel in "BWS Panel" > "Job Board".
4. Create a page or a post and insert the shortcode [jbbrd_vacancy] into the text.

[View a PDF version of Step-by-step Instruction on Job Board Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)

https://www.youtube.com/watch?v=-5mDdQmDuIc

== Frequently Asked Questions ==

= How can I create a vacancies page on my site? =

Just insert the shortcode [jbbrd_vacancy] into your page or post and save the settings on the settings page of the plugin. Attention: In order to avoid incorrect formatting of the text, the shortcode should be placed in text mode.

= How can I add a job offer on the vacancy page? =

Only administrator and the users with the role "employer" can manage job offers. Users with the role "employer" can view and sort all vacancies, as well as to create and edit their own. Roles are assigned by the administrator. The administrator can create, browse, sort, edit and delete any vacancies, CV, saved searches and search categories. Log in to your profile. Click "Vacancies" on the Panel, then click "Add vacancies". Fill in the fields, select the job category if it already exists, or create a new one. Fields marked with "*" are mandatory. Publish this vacancy by clicking on "Publish".

= How can I add a category of vacancies? =

To create or edit an existing category of vacancies, click on "Vacancies" on the admin panel, then click on the "Vacancy categories". Fill in the fields "Name", "Slug", "Description". Then add a new category by clicking on the "Add new vacancy category".

= How can I add my company`s logo to a job offer? =

Create a new vacancy, or press the "Edit" in existing one. Select "Featured Image / Set featured image". Upload an image and save the post. Recommended logo images for download are 150px by 100px.

= How can I place the login/registration form to add users with Employer and Job candidate roles? =

Insert the shortcode [jbbrd_registration] into your page or post. You can also add the login/registration form into the widget. To do it, just create a text widget and put the shortcode into the widget field. To prevent the login/registration errors, do not place more than one registration form shortcode on your website.

= Why I can't see the CV sort and send the form? =

Make sure that you have [Sender plugin](https://bestwebsoft.com/products/wordpress/plugins/sender/?k=ccd218c6d916f9735e3de54ff210e4fe) installed and activated. Register as a Job candidate to get the possibility to use a filter of vacancies and send CV.

= How can I send CV? =

Log in to your profile. In the user profile file, add CV (Doc, Docx, Pdf, Txt only). Click "Send CV" under the vacancy. The employer will receive an email with your details and a link to your CV attached file.

= Why I can't find a vacancy using a standard search form? =

Unlike standard posts, vacancies are custom post type and they are not included in the standard search form. To add a vacancy custom post type into your search, please install [Custom Search](https://bestwebsoft.com/products/wordpress/plugins/custom-search/?k=0320eef03b72c22f7448ab163f612a6d) plugin.

= What is the "archive of vacancies"? How to extract a vacancy from the archive of vacancies? =

Vacancies' validity is specified when entering a date in the "Expiry date" field. After reaching the specified date, vacancies are been automatically placed in the archive. Archive vacancies are not displayed in the front-end of the vacancies page, but they are preserved and always available for editing. You can remove the job from the archive. To do this, enter a new date in the "Expiry date" and click "Restore from archive". If you specify a wrong (already past) date in the "Expiry date", the vacancy will be available till the next archivation. You can set the time of daily archivation using the settings page of the plugin. If the field "Expiry date" is empty, the vacancy is considered to be constant.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<https://support.bestwebsoft.com>). If no, please provide the following data along with your problem's description:
- The link to the page where the problem occurs
- The name of the plugin and its version. If you are using a pro version - your order number.
- The version of your WordPress installation
- Copy and paste into the message your system status report. Please read more here: [Instruction on System Status](https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/)

== Screenshots ==

1. Vacancies page in front-end display with sorting form fields.
2. Single job offer page view.
3. Registration form in widget area.
4. Job Board display.
5. Adding new vacancy with additional fields.
6. Adding new vacancies category display with additional fields.
7. Adding new employment type display.
8. Plugin settings in WordPress admin panel with additional fields.
9. Job candidate settings on profile page.
10. Job offers for chosen category on a profile page.

== Changelog ==

= V1.1.5 - 12.07.2017 =
* Update : We updated all functionality for wordpress 4.8.

= V1.1.4 - 11.04.2017 =
* Update : The compatibility with new version of Custom Search plugin has been updated.
* Bugfix : The compatibility issues with Sender by BestWebSoft were fixed.
* Bugfix : The bug with vacancies archiving was fixed.

= V1.1.3 - 12.10.2016 =
* Update : The compatibility with new Multilanguage version updated.

= V1.1.2 - 24.08.2016 =
* NEW : Keyword field has been added to the search form to specify the search terms (for jobs titles and content).
* Update : Jobs without salary has been included to search results.

= V1.1.1 - 12.07.2016 =
* Update : 'wpautop' filter was added for job description.
* Update : We updated all functionality for wordpress 4.5.3.

= V1.1.0 - 09.05.2016 =
* Bugfix : The bug with pagination was fixed.

= V1.0.9 - 09.12.2015 =
* Bugfix : The bug with plugin menu duplicating was fixed.

= V1.0.8 - 03.11.2015 =
* NEW : A button for Job Board shortcode inserting to the content was added.
* Update : Textdomain was changed.
* Update : We updated all functionality for wordpress 4.3.1.

= V1.0.7 - 07.06.2015 =
* Bugfix : We fixed bug with editing of default employments.
* Bugfix : We fixed bug with plugin work on multisite.
* Bugfix : We fixed bug with displaying of the new jobs on the user profile page.
* New : Ability to restore settings to defaults.

= V1.0.6 - 15.05.2015 =
* Update : BWS plugins section is updated.
* Update : We updated all functionality for wordpress 4.2.2.

= V1.0.5 - 09.04.2015 =
* Bugfix : A permalinks error when using the vacancy page as blog main page was fixed
* Bugfix : Incorrect style frontend sorting form was fixed.
* Bugfix : Fixed position for job-board posts is removed.

= V1.0.4 - 26.02.2015 =
* Bugfix : Incorrect style linking was fixed.
* Update : We changed all deprecated functions in WordPress 4.1.1.

= V1.0.3 - 12.01.2015 =
* New : We added ability to search jobs for non-registered users.
* Update : We changed all deprecated functions in WordPress 4.1.

= V1.0.2 - 20.10.2014 =
* Bugfix : Bug with export/import of a database was fixed.
* Bugfix : Bug with activation function was fixed.
* New : We added a custom money unit.
* Update : We changed all deprecated functions in WordPress 4.0.

= V1.0.1 - 08.08.2014 =
* Bugfix : Security Exploit was fixed.

= V1.0.0 - 18.07.2014 =
* Bugfix : Login/registration form bugs were fixed.
* Bugfix : Session bugs were fixed.

== Upgrade Notice ==

= V1.1.5 =
* The compatibility with new WordPress version updated.

= V1.1.4 =
* The compatibility with new version of Custom Search updated.
* Bugs fixed

= V1.1.3 =
* Plugin optimization completed.

= V1.1.2 =
* Functionality expanded.

= V1.1.1 =
'wpautop' filter was added for job description. We updated all functionality for wordpress 4.5.3.

= V1.1.0 =
Ability to add custom styles.

= V1.0.9 =
The bug with plugin menu duplicating was fixed.

= V1.0.8 =
A button for Job Board shortcode inserting to the content was added. Textdomain was changed. We updated all functionality for wordpress 4.3.1.

= V1.0.7 =
We fixed bug with editing of default employments. We fixed bug with plugin work on multisite. We fixed bug with displaying of the new jobs on the user profile page. Ability to restore settings to defaults.

= V1.0.6 =
BWS plugins section is updated. We updated all functionality for wordpress 4.2.2.

= V1.0.5 =
A permalinks error when using the vacancy page as blog main page was fixed. Incorrect style frontend sorting form was fixed. Fixed position for job-board posts is removed.

= V1.0.4 =
Incorrect style linking was fixed. We changed all deprecated functions in WordPress 4.1.1.

= V1.0.3 =
We added ability to search jobs for non-registered users. We changed all deprecated functions in WordPress 4.1.

= V1.0.2 =
Bug with export/import of a database was fixed. Bug with activation function was fixed. We added a custom money unit. We changed all deprecated functions in WordPress 4.0.

= V1.0.1 =
Security Exploit was fixed.

= V1.0.0 =
Login/registration form bugs were fixed. Session bugs were fixed.