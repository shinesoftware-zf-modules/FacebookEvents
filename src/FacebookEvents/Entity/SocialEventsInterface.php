<?php
/**
* Copyright (c) 2014 Shine Software.
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions
* are met:
*
* * Redistributions of source code must retain the above copyright
* notice, this list of conditions and the following disclaimer.
*
* * Redistributions in binary form must reproduce the above copyright
* notice, this list of conditions and the following disclaimer in
* the documentation and/or other materials provided with the
* distribution.
*
* * Neither the names of the copyright holders nor the names of the
* contributors may be used to endorse or promote products derived
* from this software without specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
* "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
* LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
* FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
* COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
* INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
* BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
* CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
* LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
* ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* @package FacebookEvents
* @subpackage Entity
* @author Michelangelo Turillo <mturillo@shinesoftware.com>
* @copyright 2014 Michelangelo Turillo.
* @license http://www.opensource.org/licenses/bsd-license.php BSD License
* @link http://shinesoftware.com
* @version @@PACKAGE_VERSION@@
*/

namespace FacebookEvents\Entity;

interface SocialEventsInterface
{
    public function getId();
    public function getCode();
    public function setCode($code);
    public function getIcaluid();
    public function setIcaluid($icaluid);
    public function getStatus();
    public function setStatus($status);
    public function getSummary();
    public function setSummary($summary);
    public function getCreated();
    public function setCreated($created);
    public function getUpdated();
    public function setUpdated($updated);
    public function getUserId();
    public function setUserId($user_id);
    public function getStart();
    public function setStart($start);
    public function getEnd();
    public function setEnd($end);
    public function getDescription();
    public function setDescription($description);
    public function getLocation();
    public function setLocation($location);
    public function getEtag();
    public function setEtag($etag);
    public function getLatitude();
    public function setLatitude($latitude);
    public function getLongitude();
    public function setLongitude($longitude);
    public function getRecurrence();
    public function setRecurrence($recurrence);
    public function getNote();
    public function setNote($note);
    public function getPhoto();
    public function setPhoto($photo);
    public function getSocialnetwork();
    public function setSocialnetwork($socialnetwork);
}